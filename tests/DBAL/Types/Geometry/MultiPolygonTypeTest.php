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

namespace Bcremer\Spatial\Tests\DBAL\Types\Geometry;

use Bcremer\Spatial\PHP\Types\Geometry\LineString;
use Bcremer\Spatial\PHP\Types\Geometry\MultiPolygon;
use Bcremer\Spatial\PHP\Types\Geometry\Point;
use Bcremer\Spatial\PHP\Types\Geometry\Polygon;
use Bcremer\Spatial\Tests\Fixtures\MultiPolygonEntity;
use Bcremer\Spatial\Tests\OrmTestCase;

/**
 * MultiPolygonType tests
 *
 * @author  Derek J. Lambert <dlambert@dereklambert.com>
 * @license http://dlambert.mit-license.org MIT
 *
 * @group geometry
 */
class MultiPolygonTypeTest extends OrmTestCase
{
    protected function setUp(): void
    {
        $this->usesEntity(self::MULTIPOLYGON_ENTITY);
        parent::setUp();
    }

    public function testNullMultiPolygon(): void
    {
        $entity = new MultiPolygonEntity();

        $this->getEntityManager()->persist($entity);
        $this->getEntityManager()->flush();

        $id = $entity->getId();

        $this->getEntityManager()->clear();

        $queryEntity = $this->getEntityManager()->getRepository(self::MULTIPOLYGON_ENTITY)->find($id);

        $this->assertEquals($entity, $queryEntity);
    }

    public function testMultiPolygon(): void
    {
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
        $entity = new MultiPolygonEntity();

        $entity->setMultiPolygon(new MultiPolygon($polygons));
        $this->getEntityManager()->persist($entity);
        $this->getEntityManager()->flush();

        $id = $entity->getId();

        $this->getEntityManager()->clear();

        $queryEntity = $this->getEntityManager()->getRepository(self::MULTIPOLYGON_ENTITY)->find($id);

        $this->assertEquals($entity, $queryEntity);
    }


    public function testFindByMultiPolygon(): void
    {
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
        $entity = new MultiPolygonEntity();

        $entity->setMultiPolygon(new MultiPolygon($polygons));
        $this->getEntityManager()->persist($entity);
        $this->getEntityManager()->flush();
        $this->getEntityManager()->clear();

        $result = $this->getEntityManager()->getRepository(self::MULTIPOLYGON_ENTITY)->findByMultiPolygon(new MultiPolygon($polygons));

        $this->assertEquals($entity, $result[0]);
    }
}
