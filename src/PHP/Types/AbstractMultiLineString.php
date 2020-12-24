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

/**
 * Abstract MultiLineString object for MULTILINESTRING spatial types
 *
 * @author  Derek J. Lambert <dlambert@dereklambert.com>
 * @license http://dlambert.mit-license.org MIT
 */
abstract class AbstractMultiLineString extends AbstractGeometry
{
    /**
     * @var array[] $lineStrings
     */
    protected $lineStrings = [];

    /**
     * @param AbstractLineString[]|array[] $rings
     */
    public function __construct(array $rings)
    {
        $this->setLineStrings($rings);
    }

    /**
     * @param AbstractLineString|array[] $lineString
     *
     * @return self
     */
    public function addLineString($lineString): AbstractMultiLineString
    {
        $this->lineStrings[] = $this->validateLineStringValue($lineString);

        return $this;
    }

    /**
     * @return AbstractLineString[]
     */
    public function getLineStrings(): array
    {
        $lineStrings = [];

        for ($i = 0; $i < count($this->lineStrings); $i++) {
            $lineStrings[] = $this->getLineString($i);
        }

        return $lineStrings;
    }

    /**
     * @param int $index
     *
     * @return AbstractLineString
     */
    public function getLineString(int $index): AbstractLineString
    {
        if ($index == -1) {
            $index = count($this->lineStrings) - 1;
        }

        $lineStringClass = $this->getNamespace() . '\LineString';

        return new $lineStringClass($this->lineStrings[$index]);
    }

    /**
     * @param AbstractLineString[] $lineStrings
     *
     * @return self
     */
    public function setLineStrings(array $lineStrings): AbstractMultiLineString
    {
        $this->lineStrings = $this->validateMultiLineStringValue($lineStrings);

        return $this;
    }

    public function getType(): string
    {
        return self::MULTILINESTRING;
    }

    public function toArray(): array
    {
        return $this->lineStrings;
    }
}
