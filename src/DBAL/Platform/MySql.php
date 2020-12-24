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

namespace Bcremer\Spatial\DBAL\Platform;

use Bcremer\Spatial\DBAL\Types\AbstractSpatialType;
use Bcremer\Spatial\DBAL\Types\GeographyType;
use Bcremer\Spatial\Exception\InvalidValueException;
use Bcremer\Spatial\PHP\Types\Geography\GeographyInterface;
use Bcremer\Spatial\PHP\Types\Geometry\GeometryInterface;
use CrEOF\Geo\WKB\Parser as BinaryParser;
use CrEOF\Geo\WKT\Parser as StringParser;

/**
 * Abstract spatial platform
 *
 * @author  Derek J. Lambert <dlambert@dereklambert.com>
 * @license http://dlambert.mit-license.org MIT
 */
class MySql
{
    /**
     * Gets the SQL declaration snippet for a field of this type.
     *
     * @param array $fieldDeclaration
     *
     * @return string
     */
    public function getSQLDeclaration(array $fieldDeclaration): string
    {
        if ($fieldDeclaration['type']->getSQLType() === GeographyInterface::GEOGRAPHY) {
            return 'GEOMETRY';
        }

        return strtoupper($fieldDeclaration['type']->getSQLType());
    }

    /**
     * @param AbstractSpatialType $type
     * @param string $sqlExpr
     *
     * @return string
     */
    public function convertToPHPValueSQL(AbstractSpatialType $type, string $sqlExpr): string
    {
        return sprintf('ST_AsBinary(%s)', $sqlExpr);
    }

    /**
     * @param AbstractSpatialType $type
     * @param string $sqlExpr
     *
     * @return string
     */
    public function convertToDatabaseValueSQL(AbstractSpatialType $type, string $sqlExpr): string
    {
        return sprintf('ST_GeomFromText(%s)', $sqlExpr);
    }

    /**
     * @param AbstractSpatialType $type
     * @param string $sqlExpr
     *
     * @return GeometryInterface
     */
    public function convertStringToPHPValue(AbstractSpatialType $type, string $sqlExpr): GeometryInterface
    {
        $parser = new StringParser($sqlExpr);

        return $this->newObjectFromValue($type, $parser->parse());
    }

    /**
     * @param AbstractSpatialType $type
     * @param string $sqlExpr
     *
     * @return GeometryInterface
     */
    public function convertBinaryToPHPValue(AbstractSpatialType $type, string $sqlExpr): GeometryInterface
    {
        $parser = new BinaryParser($sqlExpr);

        return $this->newObjectFromValue($type, $parser->parse());
    }

    /**
     * @param AbstractSpatialType $type
     * @param GeometryInterface   $value
     *
     * @return string
     */
    public function convertToDatabaseValue(AbstractSpatialType $type, GeometryInterface $value): string
    {
        return sprintf('%s(%s)', strtoupper($value->getType()), $value);
    }

    /**
     * Get an array of database types that map to this Doctrine type.
     *
     * @param AbstractSpatialType $type
     *
     * @return string[]
     */
    public function getMappedDatabaseTypes(AbstractSpatialType $type): array
    {
        $sqlType = strtolower($type->getSQLType());

        if ($type instanceof GeographyType && $sqlType !== 'geography') {
            $sqlType = sprintf('geography(%s)', $sqlType);
        }

        return [$sqlType];
    }

    /**
     * Create spatial object from parsed value
     *
     * @param AbstractSpatialType $type
     * @param array               $value
     *
     * @return GeometryInterface
     * @throws InvalidValueException
     */
    private function newObjectFromValue(AbstractSpatialType $type, $value): GeometryInterface
    {
        $typeFamily = $type->getTypeFamily();
        $typeName   = strtoupper($value['type']);

        $constName = sprintf('Bcremer\Spatial\PHP\Types\Geometry\GeometryInterface::%s', $typeName);

        if (! defined($constName)) {
            // @codeCoverageIgnoreStart
            throw new InvalidValueException(sprintf('Unsupported %s type "%s".', $typeFamily, $typeName));
            // @codeCoverageIgnoreEnd
        }

        $class = sprintf('Bcremer\Spatial\PHP\Types\%s\%s', $typeFamily, constant($constName));

        return new $class($value['value']);
    }
}
