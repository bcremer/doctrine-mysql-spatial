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

namespace Bcremer\Spatial\Tests\ORM\Query\AST\Functions\MySql;

use Bcremer\Spatial\PHP\Types\Geometry\LineString;
use Bcremer\Spatial\PHP\Types\Geometry\Point;
use Bcremer\Spatial\PHP\Types\Geometry\Polygon;
use Bcremer\Spatial\Tests\Fixtures\PolygonEntity;
use Bcremer\Spatial\Tests\OrmTestCase;

/**
 * Contains DQL function tests
 *
 * @author  Derek J. Lambert <dlambert@dereklambert.com>
 * @license http://dlambert.mit-license.org MIT
 *
 * @group dql
 */
class STContainsTest extends OrmTestCase
{
    protected function setUp(): void
    {
        $this->usesEntity(self::POLYGON_ENTITY);
        $this->usesType('point');

        parent::setUp();
    }

    /**
     * @group geometry
     */
    public function testSelectSTContains(): void
    {
        $lineString1 = new LineString([
            new Point(0, 0),
            new Point(10, 0),
            new Point(10, 10),
            new Point(0, 10),
            new Point(0, 0)
        ]);
        $lineString2 = new LineString([
            new Point(5, 5),
            new Point(5, 7),
            new Point(7, 7),
            new Point(7, 5),
            new Point(5, 5)
        ]);

        $entity1 = new PolygonEntity();
        $entity1->setPolygon(new Polygon([$lineString1]));
        $this->getEntityManager()->persist($entity1);

        $entity2 = new PolygonEntity();
        $entity2->setPolygon(new Polygon([$lineString2]));
        $this->getEntityManager()->persist($entity2);

        $this->getEntityManager()->flush();
        $this->getEntityManager()->clear();

        $query  = $this->getEntityManager()->createQuery('SELECT p, ST_Contains(p.polygon, ST_GeomFromText(:p1)) FROM Bcremer\Spatial\Tests\Fixtures\PolygonEntity p');
        $query->setParameter('p1', 'POINT(2 2)', 'string');
        $result = $query->getResult();

        $this->assertCount(2, $result);
        $this->assertEquals($entity1, $result[0][0]);
        $this->assertEquals(1, $result[0][1]);
        $this->assertEquals($entity2, $result[1][0]);
        $this->assertEquals(0, $result[1][1]);
    }

    /**
     * @group geometry
     */
    public function testSTContainsWhereParameter(): void
    {
        $lineString1 = new LineString([
            new Point(0, 0),
            new Point(10, 0),
            new Point(10, 10),
            new Point(0, 10),
            new Point(0, 0)
        ]);
        $lineString2 = new LineString([
            new Point(5, 5),
            new Point(5, 7),
            new Point(7, 7),
            new Point(7, 5),
            new Point(5, 5)
        ]);

        $entity1 = new PolygonEntity();
        $entity1->setPolygon(new Polygon([$lineString1]));
        $this->getEntityManager()->persist($entity1);

        $entity2 = new PolygonEntity();
        $entity2->setPolygon(new Polygon([$lineString1, $lineString2])); // polygon with hole
        $this->getEntityManager()->persist($entity2);

        $this->getEntityManager()->flush();
        $this->getEntityManager()->clear();

        $query  = $this->getEntityManager()->createQuery('SELECT p FROM Bcremer\Spatial\Tests\Fixtures\PolygonEntity p WHERE ST_Contains(p.polygon, ST_GeomFromText(:p1)) = 1');
        $query->setParameter('p1', 'POINT(6 6)', 'string'); // POINT(6 6) is inside the hole
        $result = $query->getResult();

        $this->assertCount(1, $result);
        $this->assertEquals($entity1, $result[0]);

        $this->getEntityManager()->clear();

        $query  = $this->getEntityManager()->createQuery('SELECT p FROM Bcremer\Spatial\Tests\Fixtures\PolygonEntity p WHERE ST_Contains(p.polygon, ST_GeomFromText(:p1)) = 1');
        $query->setParameter('p1', 'POINT(2 2)', 'string');
        $result = $query->getResult();

        $this->assertCount(2, $result);
        $this->assertEquals($entity1, $result[0]);
        $this->assertEquals($entity2, $result[1]);
    }
}
