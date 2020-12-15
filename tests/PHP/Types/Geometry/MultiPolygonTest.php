<?php

namespace CrEOF\Spatial\Tests\PHP\Types\Geometry;

use CrEOF\Spatial\PHP\Types\Geometry\LineString;
use CrEOF\Spatial\PHP\Types\Geometry\MultiPolygon;
use CrEOF\Spatial\PHP\Types\Geometry\Point;
use CrEOF\Spatial\PHP\Types\Geometry\Polygon;
use PHPUnit\Framework\TestCase;

/**
 * Polygon object tests
 *
 * @author  Derek J. Lambert <dlambert@dereklambert.com>
 * @license http://dlambert.mit-license.org MIT
 *
 * @group php
 */
class MultiPolygonTest extends TestCase
{
    public function testEmptyMultiPolygon(): void
    {
        $multiPolygon = new MultiPolygon([]);

        $this->assertEmpty($multiPolygon->getPolygons());
    }

    public function testSolidMultiPolygonFromObjectsToArray(): void
    {
        $expected = [
            [
                [
                    [0, 0],
                    [10, 0],
                    [10, 10],
                    [0, 10],
                    [0, 0]
                ]
            ],
            [
                [
                    [5, 5],
                    [7, 5],
                    [7, 7],
                    [5, 7],
                    [5, 5]
                ]
            ]
        ];

        $polygons = [
            new Polygon(
                [
                    new LineString(
                        [
                            new Point(0, 0),
                            new Point(10, 0),
                            new Point(10, 10),
                            new Point(0, 10),
                            new Point(0, 0)
                        ]
                    )
                ]
            ),
            new Polygon(
                [
                    new LineString(
                        [
                            new Point(5, 5),
                            new Point(7, 5),
                            new Point(7, 7),
                            new Point(5, 7),
                            new Point(5, 5)
                        ]
                    )
                ]
            )
        ];

        $multiPolygon = new MultiPolygon($polygons);

        $this->assertEquals($expected, $multiPolygon->toArray());
    }

    public function testSolidMultiPolygonFromArraysGetPolygons(): void
    {
        $expected = [
            new Polygon(
                [
                    new LineString(
                        [
                            new Point(0, 0),
                            new Point(10, 0),
                            new Point(10, 10),
                            new Point(0, 10),
                            new Point(0, 0)
                        ]
                    )
                ]
            ),
            new Polygon(
                [
                    new LineString(
                        [
                            new Point(5, 5),
                            new Point(7, 5),
                            new Point(7, 7),
                            new Point(5, 7),
                            new Point(5, 5)
                        ]
                    )
                ]
            )
        ];

        $polygons = [
            [
                [
                    [0, 0],
                    [10, 0],
                    [10, 10],
                    [0, 10],
                    [0, 0]
                ]
            ],
            [
                [
                    [5, 5],
                    [7, 5],
                    [7, 7],
                    [5, 7],
                    [5, 5]
                ]
            ]
        ];


        $multiPolygon = new MultiPolygon($polygons);

        $this->assertEquals($expected, $multiPolygon->getPolygons());
    }


    public function testSolidMultiPolygonAddPolygon(): void
    {
        $expected = [
            new Polygon(
                [
                    new LineString(
                        [
                            new Point(0, 0),
                            new Point(10, 0),
                            new Point(10, 10),
                            new Point(0, 10),
                            new Point(0, 0)
                        ]
                    )
                ]
            ),
            new Polygon(
                [
                    new LineString(
                        [
                            new Point(5, 5),
                            new Point(7, 5),
                            new Point(7, 7),
                            new Point(5, 7),
                            new Point(5, 5)
                        ]
                    )
                ]
            )
        ];


        $polygon =  new Polygon(
            [
                new LineString(
                    [
                        new Point(0, 0),
                        new Point(10, 0),
                        new Point(10, 10),
                        new Point(0, 10),
                        new Point(0, 0),
                    ]
                ),
            ]
        );


        $multiPolygon = new MultiPolygon([$polygon]);

        $multiPolygon->addPolygon(
            [
                [
                    new Point(5, 5),
                    new Point(7, 5),
                    new Point(7, 7),
                    new Point(5, 7),
                    new Point(5, 5),
                ],
            ]
        );

        $this->assertEquals($expected, $multiPolygon->getPolygons());
    }



    public function testMultiPolygonFromObjectsGetSinglePolygon(): void
    {
        $polygon1 = new Polygon(
            [
                new LineString(
                    [
                        new Point(0, 0),
                        new Point(10, 0),
                        new Point(10, 10),
                        new Point(0, 10),
                        new Point(0, 0)
                    ]
                )
            ]
        );
        $polygon2 = new Polygon(
            [
                new LineString(
                    [
                        new Point(5, 5),
                        new Point(7, 5),
                        new Point(7, 7),
                        new Point(5, 7),
                        new Point(5, 5)
                    ]
                )
            ]
        );
        $multiPolygon = new MultiPolygon([$polygon1, $polygon2]);

        $this->assertEquals($polygon1, $multiPolygon->getPolygon(0));
    }

    public function testMultiPolygonFromObjectsGetLastPolygon(): void
    {
        $polygon1 = new Polygon(
            [
                new LineString(
                    [
                        new Point(0, 0),
                        new Point(10, 0),
                        new Point(10, 10),
                        new Point(0, 10),
                        new Point(0, 0)
                    ]
                )
            ]
        );
        $polygon2 = new Polygon(
            [
                new LineString(
                    [
                        new Point(5, 5),
                        new Point(7, 5),
                        new Point(7, 7),
                        new Point(5, 7),
                        new Point(5, 5)
                    ]
                )
            ]
        );
        $multiPolygon = new MultiPolygon([$polygon1, $polygon2]);

        $this->assertEquals($polygon2, $multiPolygon->getPolygon(-1));
    }

    public function testSolidMultiPolygonFromArraysToString(): void
    {
        $expected = '((0 0,10 0,10 10,0 10,0 0)),((5 5,7 5,7 7,5 7,5 5))';
        $polygons = [
            [
                [
                    [0, 0],
                    [10, 0],
                    [10, 10],
                    [0, 10],
                    [0, 0]
                ]
            ],
            [
                [
                    [5, 5],
                    [7, 5],
                    [7, 7],
                    [5, 7],
                    [5, 5]
                ]
            ]
        ];
        $multiPolygon = new MultiPolygon($polygons);
        $result  = (string) $multiPolygon;

        $this->assertEquals($expected, $result);
    }

    public function testJson(): void
    {
        $expected = '{"type":"MultiPolygon","coordinates":[[[[0,0],[10,0],[10,10],[0,10],[0,0]]],[[[5,5],[7,5],[7,7],[5,7],[5,5]]]]}';
        $polygons = [
            [
                [
                    [0, 0],
                    [10, 0],
                    [10, 10],
                    [0, 10],
                    [0, 0]
                ]
            ],
            [
                [
                    [5, 5],
                    [7, 5],
                    [7, 7],
                    [5, 7],
                    [5, 5]
                ]
            ]
        ];
        $multiPolygon = new MultiPolygon($polygons);

        $this->assertEquals($expected, $multiPolygon->toJson());
    }
}
