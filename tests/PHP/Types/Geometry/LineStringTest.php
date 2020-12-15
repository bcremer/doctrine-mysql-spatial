<?php

/**
 * Copyright (C) 2012 Derek J. Lambert
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

namespace CrEOF\Spatial\Tests\PHP\Types\Geometry;

use CrEOF\Spatial\Exception\InvalidValueException;
use CrEOF\Spatial\PHP\Types\Geometry\LineString;
use CrEOF\Spatial\PHP\Types\Geometry\Point;
use PHPUnit\Framework\TestCase;

/**
 * LineString object tests
 *
 * @author  Derek J. Lambert <dlambert@dereklambert.com>
 * @license http://dlambert.mit-license.org MIT
 *
 * @group php
 */
class LineStringTest extends TestCase
{
    public function testEmptyLineString(): void
    {
        $lineString = new LineString([]);

        $this->assertEmpty($lineString->getPoints());
    }

    public function testLineStringFromObjectsToArray(): void
    {
        $expected = [
            [0, 0],
            [1, 1],
            [2, 2],
            [3, 3]
        ];
        $lineString = new LineString(
            [
            new Point(0, 0),
            new Point(1, 1),
            new Point(2, 2),
            new Point(3, 3)
            ]
        );

        $this->assertCount(4, $lineString->getPoints());
        $this->assertEquals($expected, $lineString->toArray());
    }

    public function testLineStringFromArraysGetPoints(): void
    {
        $expected = [
            new Point(0, 0),
            new Point(1, 1),
            new Point(2, 2),
            new Point(3, 3)
        ];
        $lineString = new LineString(
            [
                [0, 0],
                [1, 1],
                [2, 2],
                [3, 3]
            ]
        );
        $actual = $lineString->getPoints();

        $this->assertCount(4, $actual);
        $this->assertEquals($expected, $actual);
    }

    public function testLineStringFromArraysGetSinglePoint(): void
    {
        $expected = new Point(1, 1);
        $lineString = new LineString(
            [
                [0, 0],
                [1, 1],
                [2, 2],
                [3, 3]
            ]
        );
        $actual = $lineString->getPoint(1);

        $this->assertEquals($expected, $actual);
    }

    public function testLineStringFromArraysGetLastPoint(): void
    {
        $expected = new Point(3, 3);
        $lineString = new LineString(
            [
                [0, 0],
                [1, 1],
                [2, 2],
                [3, 3]
            ]
        );
        $actual = $lineString->getPoint(-1);

        $this->assertEquals($expected, $actual);
    }

    public function testLineStringFromArraysIsOpen(): void
    {
        $lineString = new LineString(
            [
                [0, 0],
                [1, 1],
                [2, 2],
                [3, 3]
            ]
        );

        $this->assertFalse($lineString->isClosed());
    }

    public function testLineStringFromArraysIsClosed(): void
    {
        $lineString = new LineString(
            [
                [0, 0],
                [0, 5],
                [5, 0],
                [0, 0]
            ]
        );

        $this->assertTrue($lineString->isClosed());
    }

    /**
     * Test LineString bad parameter
     */
    public function testBadLineString(): void
    {
        $this->expectException(InvalidValueException::class);
        $this->expectExceptionMessage('Invalid LineString Point value of type "integer"');
        new LineString([1, 2, 3 ,4]);
    }

    public function testLineStringFromArraysToString(): void
    {
        $expected = '0 0,0 5,5 0,0 0';
        $lineString = new LineString(
            [
                [0, 0],
                [0, 5],
                [5, 0],
                [0, 0]
            ]
        );

        $this->assertEquals($expected, (string) $lineString);
    }

    public function testJson(): void
    {
        $expected = "{\"type\":\"LineString\",\"coordinates\":[[0,0],[0,5],[5,0],[0,0]]}";

        $lineString = new LineString(
            [
                [0, 0],
                [0, 5],
                [5, 0],
                [0, 0]
            ]
        );
        $this->assertEquals($expected, $lineString->toJson());
    }
}
