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
use CrEOF\Spatial\PHP\Types\Geometry\MultiPoint;
use CrEOF\Spatial\PHP\Types\Geometry\Point;
use PHPUnit\Framework\TestCase;

/**
 * MultiPoint object tests
 *
 * @author  Derek J. Lambert <dlambert@dereklambert.com>
 * @license http://dlambert.mit-license.org MIT
 *
 * @group php
 */
class MultiPointTest extends TestCase
{
    public function testEmptyMultiPoint(): void
    {
        $multiPoint = new MultiPoint([]);

        $this->assertEmpty($multiPoint->getPoints());
    }

    public function testMultiPointFromObjectsToArray(): void
    {
        $expected = [
            [0, 0],
            [1, 1],
            [2, 2],
            [3, 3]
        ];
        $multiPoint = new MultiPoint(
            [
            new Point(0, 0),
            new Point(1, 1),
            new Point(2, 2),
            new Point(3, 3)
            ]
        );

        $this->assertCount(4, $multiPoint->getPoints());
        $this->assertEquals($expected, $multiPoint->toArray());
    }

    public function testMultiPointFromArraysGetPoints(): void
    {
        $expected = [
            new Point(0, 0),
            new Point(1, 1),
            new Point(2, 2),
            new Point(3, 3)
        ];
        $multiPoint = new MultiPoint(
            [
                [0, 0],
                [1, 1],
                [2, 2],
                [3, 3]
            ]
        );
        $actual = $multiPoint->getPoints();

        $this->assertCount(4, $actual);
        $this->assertEquals($expected, $actual);
    }



    public function testMultiPointAddPoints(): void
    {
        $expected = [
            new Point(0, 0),
            new Point(1, 1),
            new Point(2, 2),
            new Point(3, 3)
        ];
        $multiPoint = new MultiPoint(
            [
                [0, 0],
                [1, 1],
            ]
        );

        $multiPoint
            ->addPoint([2, 2])
            ->addPoint([3, 3])
        ;

        $actual = $multiPoint->getPoints();

        $this->assertCount(4, $actual);
        $this->assertEquals($expected, $actual);
    }

    public function testMultiPointFromArraysGetSinglePoint(): void
    {
        $expected = new Point(1, 1);
        $multiPoint = new MultiPoint(
            [
                [0, 0],
                [1, 1],
                [2, 2],
                [3, 3]
            ]
        );
        $actual = $multiPoint->getPoint(1);

        $this->assertEquals($expected, $actual);
    }

    public function testMultiPointFromArraysGetLastPoint(): void
    {
        $expected = new Point(3, 3);
        $multiPoint = new MultiPoint(
            [
                [0, 0],
                [1, 1],
                [2, 2],
                [3, 3]
            ]
        );
        $actual = $multiPoint->getPoint(-1);

        $this->assertEquals($expected, $actual);
    }

    /**
     * Test MultiPoint bad parameter
     */
    public function testBadLineString(): void
    {
        $this->expectException(InvalidValueException::class);
        $this->expectExceptionMessage('Invalid MultiPoint Point value of type "integer"');
        new MultiPoint([1, 2, 3 ,4]);
    }

    public function testMultiPointFromArraysToString(): void
    {
        $expected   = '0 0,0 5,5 0,0 0';
        $multiPoint = new MultiPoint(
            [
                [0, 0],
                [0, 5],
                [5, 0],
                [0, 0]
            ]
        );

        $this->assertEquals($expected, (string) $multiPoint);
    }

    public function testJson(): void
    {
        $expected   = '{"type":"MultiPoint","coordinates":[[0,0],[0,5],[5,0],[0,0]]}';
        $multiPoint = new MultiPoint(
            [
                [0, 0],
                [0, 5],
                [5, 0],
                [0, 0]
            ]
        );

        $this->assertEquals($expected, $multiPoint->toJson());
    }
}
