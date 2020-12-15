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

namespace CrEOF\Spatial\PHP\Types;

use CrEOF\Geo\String\Exception\RangeException;
use CrEOF\Geo\String\Exception\UnexpectedValueException;
use CrEOF\Geo\String\Parser;
use CrEOF\Spatial\Exception\InvalidValueException;

/**
 * Abstract point object for POINT spatial types
 *
 * http://stackoverflow.com/questions/7309121/preferred-order-of-writing-latitude-longitude-tuples
 * http://docs.geotools.org/latest/userguide/library/referencing/order.html
 *
 * @author  Derek J. Lambert <dlambert@dereklambert.com>
 * @license http://dlambert.mit-license.org MIT
 */
abstract class AbstractPoint extends AbstractGeometry
{
    protected float $x;
    protected float $y;

    public function __construct()
    {
        $args = func_get_args();

        if (count($args) === 2 && $this->isValidTuple($args[0], $args[1])) {
            $this->setX($args[0]);
            $this->setY($args[1]);

            return;
        }

        $isArgsArray = count($args) === 1 && is_array($args[0]) && count($args[0]) === 2;
        if ($isArgsArray && $this->isValidTuple($args[0][0], $args[0][1])) {
            $this->setX($args[0][0]);
            $this->setY($args[0][1]);

            return;
        }

        $args = array_map(
            static fn($value) => is_array($value) ? 'Array' : sprintf('"%s"', $value),
            $args
        );

        throw new InvalidValueException(
            sprintf('Invalid parameters passed to %s::%s: %s', get_class($this), '__construct', implode(', ', $args))
        );
    }

    public function setX(string $x): AbstractPoint
    {
        $parser = new Parser($x);

        try {
            $this->x = (float) $parser->parse();
        } catch (RangeException $e) {
            throw new InvalidValueException($e->getMessage(), $e->getCode(), $e->getPrevious());
        } catch (UnexpectedValueException $e) {
            throw new InvalidValueException($e->getMessage(), $e->getCode(), $e->getPrevious());
        }

        return $this;
    }

    public function getX(): float
    {
        return $this->x;
    }
    public function setY(string $y): AbstractPoint
    {
        $parser = new Parser($y);

        try {
            $this->y = (float) $parser->parse();
        } catch (RangeException $e) {
            throw new InvalidValueException($e->getMessage(), $e->getCode(), $e->getPrevious());
        } catch (UnexpectedValueException $e) {
            throw new InvalidValueException($e->getMessage(), $e->getCode(), $e->getPrevious());
        }

        return $this;
    }

    public function getY(): float
    {
        return $this->y;
    }


    /**
     * @param mixed $latitude
     *
     * @return self
     */
    public function setLatitude($latitude): AbstractPoint
    {
        return $this->setY($latitude);
    }

    /**
     * @return float
     */
    public function getLatitude(): float
    {
        return $this->getY();
    }

    /**
     * @param mixed $longitude
     *
     * @return self
     */
    public function setLongitude($longitude): AbstractPoint
    {
        return $this->setX($longitude);
    }

    /**
     * @return float
     */
    public function getLongitude(): float
    {
        return $this->getX();
    }

    public function getType(): string
    {
        return self::POINT;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [$this->x, $this->y];
    }

    private function isValidTuple($x, $y): bool
    {
        return ((is_numeric($x) || is_string($x)) && (is_numeric($y) || is_string($y)));
    }
}
