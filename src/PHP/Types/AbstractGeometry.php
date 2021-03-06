<?php

/**
 * Copyright (C) 2015 Derek J. Lambert
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

namespace Bcremer\Spatial\PHP\Types;

use Bcremer\Spatial\Exception\InvalidValueException;
use Bcremer\Spatial\PHP\Types\Geometry\GeometryInterface;

/**
 * Abstract geometry object for spatial types
 *
 * @author  Derek J. Lambert <dlambert@dereklambert.com>
 * @license http://dlambert.mit-license.org MIT
 */
abstract class AbstractGeometry implements GeometryInterface
{
    abstract public function toArray(): array;

    public function __toString(): string
    {
        $type   = strtoupper($this->getType());
        $method = 'toString' . $type;

        return $this->$method($this->toArray());
    }

    public function toJson(): string
    {
        $json['type'] = $this->getType();
        $json['coordinates'] = $this->toArray();

        return json_encode($json, JSON_THROW_ON_ERROR);
    }

    /**
     * @param AbstractPoint|array $point
     *
     * @return array
     * @throws InvalidValueException
     */
    protected function validatePointValue($point): array
    {
        switch (true) {
            case ($point instanceof AbstractPoint):
                return $point->toArray();
            case (is_array($point) && count($point) == 2 && is_numeric($point[0]) && is_numeric($point[1])):
                return array_values($point);
            default:
                throw new InvalidValueException(sprintf('Invalid %s Point value of type "%s"', $this->getType(), (is_object($point) ? get_class($point) : gettype($point))));
        }
    }

    /**
     * @param AbstractLineString|array[] $ring
     *
     * @return array[]
     * @throws InvalidValueException
     */
    protected function validateRingValue($ring)
    {
        switch (true) {
            case ($ring instanceof AbstractLineString):
                $ring = $ring->toArray();
                break;
            case (is_array($ring)):
                break;
            default:
                throw new InvalidValueException(sprintf('Invalid %s LineString value of type "%s"', $this->getType(), (is_object($ring) ? get_class($ring) : gettype($ring))));
        }

        $ring = $this->validateLineStringValue($ring);

        if ($ring[0] !== end($ring)) {
            throw new InvalidValueException(sprintf('Invalid polygon, ring "(%s)" is not closed', $this->toStringLineString($ring)));
        }

        return $ring;
    }

    /**
     * @param AbstractLineString|AbstractPoint[]|array[] $points
     *
     * @return array[]
     */
    protected function validateMultiPointValue($points)
    {
        if ($points instanceof GeometryInterface) {
            $points = $points->toArray();
        }

        foreach ($points as &$point) {
            $point = $this->validatePointValue($point);
        }

        return $points;
    }

    /**
     * @param AbstractLineString|AbstractPoint[]|array[] $lineString
     *
     * @return array[]
     */
    protected function validateLineStringValue($lineString)
    {
        return $this->validateMultiPointValue($lineString);
    }

    /**
     * @param AbstractLineString[] $rings
     *
     * @return array
     */
    protected function validatePolygonValue(array $rings): array
    {
        foreach ($rings as &$ring) {
            $ring = $this->validateRingValue($ring);
        }

        return $rings;
    }

    /**
     * @param AbstractPolygon[] $polygons
     *
     * @return array
     */
    protected function validateMultiPolygonValue(array $polygons): array
    {
        foreach ($polygons as &$polygon) {
            if ($polygon instanceof GeometryInterface) {
                $polygon = $polygon->toArray();
            }
            $polygon = $this->validatePolygonValue($polygon);
        }

        return $polygons;
    }

    /**
     * @param AbstractLineString[] $lineStrings
     *
     * @return array
     */
    protected function validateMultiLineStringValue(array $lineStrings): array
    {
        foreach ($lineStrings as &$lineString) {
            $lineString = $this->validateLineStringValue($lineString);
        }

        return $lineStrings;
    }

    /**
     * @return string
     */
    protected function getNamespace(): string
    {
        $class = get_class($this);

        return substr($class, 0, strrpos($class, '\\') - strlen($class));
    }

    /**
     * @param array $point
     *
     * @return string
     */
    private function toStringPoint(array $point): string
    {
        $localeIndependentFloatToString = static fn($number): string => sprintf('%.8F', $number);

        $removeTrailingZeroAfterDecimalPoint = static fn(string $number): string => (strpos($number, '.') !== false)
            ? rtrim(rtrim($number, '0'), '.')
            : $number;

        return sprintf(
            '%s %s',
            $removeTrailingZeroAfterDecimalPoint($localeIndependentFloatToString($point[0])),
            $removeTrailingZeroAfterDecimalPoint($localeIndependentFloatToString($point[1])),
        );
    }

    /**
     * @param array[] $multiPoint
     *
     * @return string
     */
    private function toStringMultiPoint(array $multiPoint): string
    {
        $strings = [];

        foreach ($multiPoint as $point) {
            $strings[] = $this->toStringPoint($point);
        }

        return implode(',', $strings);
    }

    /**
     * @param array[] $lineString
     *
     * @return string
     */
    private function toStringLineString(array $lineString): string
    {
        return $this->toStringMultiPoint($lineString);
    }

    /**
     * @param array[] $multiLineString
     *
     * @return string
     */
    private function toStringMultiLineString(array $multiLineString): string
    {
        $strings = null;

        foreach ($multiLineString as $lineString) {
            $strings[] = '(' . $this->toStringLineString($lineString) . ')';
        }

        return implode(',', $strings);
    }

    /**
     * @param array[] $polygon
     *
     * @return string
     */
    private function toStringPolygon(array $polygon): string
    {
        return $this->toStringMultiLineString($polygon);
    }

    /**
     * @param array[] $multiPolygon
     *
     * @return string
     */
    private function toStringMultiPolygon(array $multiPolygon): string
    {
        $strings = null;

        foreach ($multiPolygon as $polygon) {
            $strings[] = '(' . $this->toStringPolygon($polygon) . ')';
        }

        return implode(',', $strings);
    }
}
