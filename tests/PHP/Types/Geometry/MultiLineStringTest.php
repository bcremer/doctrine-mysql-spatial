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

namespace Bcremer\Spatial\Tests\PHP\Types\Geometry;

use Bcremer\Spatial\PHP\Types\Geometry\LineString;
use Bcremer\Spatial\PHP\Types\Geometry\MultiLineString;
use Bcremer\Spatial\PHP\Types\Geometry\Point;
use PHPUnit\Framework\TestCase;

/**
 * MultiLineString object tests
 *
 * @author  Derek J. Lambert <dlambert@dereklambert.com>
 * @license http://dlambert.mit-license.org MIT
 *
 * @group php
 */
class MultiLineStringTest extends TestCase
{
    public function testEmptyMultiLineString(): void
    {
        $multiLineString = new MultiLineString([]);

        $this->assertEmpty($multiLineString->getLineStrings());
    }

    public function testMultiLineStringFromObjectsToArray(): void
    {
        $expected = [
            [
                [0, 0],
                [10, 0],
                [10, 10],
                [0, 10],
                [0, 0]
            ],
            [
                [0, 0],
                [10, 0],
                [10, 10],
                [0, 10],
                [0, 0]
            ]
        ];
        $lineStrings = [
            new LineString(
                [
                    new Point(0, 0),
                    new Point(10, 0),
                    new Point(10, 10),
                    new Point(0, 10),
                    new Point(0, 0)
                ]
            ),
            new LineString(
                [
                    new Point(0, 0),
                    new Point(10, 0),
                    new Point(10, 10),
                    new Point(0, 10),
                    new Point(0, 0)
                ]
            )
        ];

        $multiLineString = new MultiLineString($lineStrings);

        $this->assertEquals($expected, $multiLineString->toArray());
    }

    public function testSolidMultiLineStringFromArraysGetRings(): void
    {
        $expected = [
            new LineString(
                [
                    new Point(0, 0),
                    new Point(10, 0),
                    new Point(10, 10),
                    new Point(0, 10),
                    new Point(0, 0)
                ]
            ),
            new LineString(
                [
                    new Point(0, 0),
                    new Point(10, 0),
                    new Point(10, 10),
                    new Point(0, 10),
                    new Point(0, 0)
                ]
            )
        ];
        $rings = [
            [
                [0, 0],
                [10, 0],
                [10, 10],
                [0, 10],
                [0, 0]
            ],
            [
                [0, 0],
                [10, 0],
                [10, 10],
                [0, 10],
                [0, 0]
            ]
        ];

        $multiLineString = new MultiLineString($rings);

        $this->assertEquals($expected, $multiLineString->getLineStrings());
    }



    public function testSolidMultiLineStringAddRings(): void
    {
        $expected = [
            new LineString(
                [
                    new Point(0, 0),
                    new Point(10, 0),
                    new Point(10, 10),
                    new Point(0, 10),
                    new Point(0, 0)
                ]
            ),
            new LineString(
                [
                    new Point(0, 0),
                    new Point(10, 0),
                    new Point(10, 10),
                    new Point(0, 10),
                    new Point(0, 0)
                ]
            )
        ];
        $rings = [
            [
                [0, 0],
                [10, 0],
                [10, 10],
                [0, 10],
                [0, 0]
            ],
        ];

        $multiLineString = new MultiLineString($rings);

        $multiLineString->addLineString(
            [
                [0, 0],
                [10, 0],
                [10, 10],
                [0, 10],
                [0, 0]
            ]
        );

        $this->assertEquals($expected, $multiLineString->getLineStrings());
    }

    public function testMultiLineStringFromObjectsGetSingleLineString(): void
    {
        $lineString1 = new LineString(
            [
                new Point(0, 0),
                new Point(10, 0),
                new Point(10, 10),
                new Point(0, 10),
                new Point(0, 0)
            ]
        );
        $lineString2 = new LineString(
            [
                new Point(5, 5),
                new Point(7, 5),
                new Point(7, 7),
                new Point(5, 7),
                new Point(5, 5)
            ]
        );
        $multiLineString = new MultiLineString([$lineString1, $lineString2]);

        $this->assertEquals($lineString1, $multiLineString->getLineString(0));
    }

    public function testMultiLineStringFromObjectsGetLastLineString(): void
    {
        $lineString1 = new LineString(
            [
                new Point(0, 0),
                new Point(10, 0),
                new Point(10, 10),
                new Point(0, 10),
                new Point(0, 0)
            ]
        );
        $lineString2 = new LineString(
            [
                new Point(5, 5),
                new Point(7, 5),
                new Point(7, 7),
                new Point(5, 7),
                new Point(5, 5)
            ]
        );
        $polygon = new MultiLineString([$lineString1, $lineString2]);

        $this->assertEquals($lineString2, $polygon->getLineString(-1));
    }

    public function testMultiLineStringFromArraysToString(): void
    {
        $expected = '(0 0,10 0,10 10,0 10,0 0),(0 0,10 0,10 10,0 10,0 0)';
        $lineStrings = [
            [
                [0, 0],
                [10, 0],
                [10, 10],
                [0, 10],
                [0, 0]
            ],
            [
                [0, 0],
                [10, 0],
                [10, 10],
                [0, 10],
                [0, 0]
            ]
        ];
        $multiLineString = new MultiLineString($lineStrings);
        $result  = (string) $multiLineString;

        $this->assertEquals($expected, $result);
    }

    public function testJson(): void
    {
        $expected = '{"type":"MultiLineString","coordinates":[[[0,0],[10,0],[10,10],[0,10],[0,0]],[[0,0],[10,0],[10,10],[0,10],[0,0]]]}';
        $lineStrings = [
            [
                [0, 0],
                [10, 0],
                [10, 10],
                [0, 10],
                [0, 0]
            ],
            [
                [0, 0],
                [10, 0],
                [10, 10],
                [0, 10],
                [0, 0]
            ]
        ];
        $multiLineString = new MultiLineString($lineStrings);

        $this->assertEquals($expected, $multiLineString->toJson());
    }
}
