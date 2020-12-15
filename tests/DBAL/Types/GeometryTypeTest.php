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

namespace CrEOF\Spatial\Tests\DBAL\Types;

use CrEOF\Spatial\Exception\InvalidValueException;
use CrEOF\Spatial\PHP\Types\Geometry\LineString;
use CrEOF\Spatial\PHP\Types\Geometry\Point;
use CrEOF\Spatial\PHP\Types\Geometry\Polygon;
use CrEOF\Spatial\Tests\Fixtures\GeometryEntity;
use CrEOF\Spatial\Tests\Fixtures\NoHintGeometryEntity;
use CrEOF\Spatial\Tests\OrmTestCase;

/**
 * Doctrine GeometryType tests
 *
 * @author  Derek J. Lambert <dlambert@dereklambert.com>
 * @license http://dlambert.mit-license.org MIT
 *
 * @group geometry
 */
class GeometryTypeTest extends OrmTestCase
{
    protected function setUp(): void
    {
        $this->usesEntity(self::GEOMETRY_ENTITY);
        $this->usesEntity(self::NO_HINT_GEOMETRY_ENTITY);
        parent::setUp();
    }

    public function testNullGeometry(): void
    {
        $entity = new GeometryEntity();

        $this->getEntityManager()->persist($entity);
        $this->getEntityManager()->flush();

        $id = $entity->getId();

        $this->getEntityManager()->clear();

        $queryEntity = $this->getEntityManager()->getRepository(self::GEOMETRY_ENTITY)->find($id);

        $this->assertEquals($entity, $queryEntity);
    }

    public function testPointGeometry(): void
    {
        $entity = new GeometryEntity();

        $entity->setGeometry(new Point(1, 1));
        $this->getEntityManager()->persist($entity);
        $this->getEntityManager()->flush();

        $id = $entity->getId();

        $this->getEntityManager()->clear();

        $queryEntity = $this->getEntityManager()->getRepository(self::GEOMETRY_ENTITY)->find($id);

        $this->assertEquals($entity, $queryEntity);
    }

    public function testLineStringGeometry(): void
    {
        $entity = new GeometryEntity();

        $entity->setGeometry(new LineString(
            [
                 new Point(0, 0),
                 new Point(1, 1)
                                 ]
        ));
        $this->getEntityManager()->persist($entity);
        $this->getEntityManager()->flush();

        $id = $entity->getId();

        $this->getEntityManager()->clear();

        $queryEntity = $this->getEntityManager()->getRepository(self::GEOMETRY_ENTITY)->find($id);

        $this->assertEquals($entity, $queryEntity);
    }

    public function testPolygonGeometry(): void
    {
        $entity = new GeometryEntity();

        $rings = [
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

        $entity->setGeometry(new Polygon($rings));
        $this->getEntityManager()->persist($entity);
        $this->getEntityManager()->flush();

        $id = $entity->getId();

        $this->getEntityManager()->clear();

        $queryEntity = $this->getEntityManager()->getRepository(self::GEOMETRY_ENTITY)->find($id);

        $this->assertEquals($entity, $queryEntity);
    }

    public function testBadGeometryValue(): void
    {
        $this->expectException(InvalidValueException::class);
        $this->expectExceptionMessage('Geometry column values must implement GeometryInterface');
        $entity = new NoHintGeometryEntity();

        $entity->setGeometry('POINT(0 0)');
        $this->getEntityManager()->persist($entity);
        $this->getEntityManager()->flush();
    }
}
