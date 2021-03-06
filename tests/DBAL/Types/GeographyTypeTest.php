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

namespace Bcremer\Spatial\Tests\DBAL\Types;

use Bcremer\Spatial\PHP\Types\Geography\LineString;
use Bcremer\Spatial\PHP\Types\Geography\Point;
use Bcremer\Spatial\PHP\Types\Geography\Polygon;
use Bcremer\Spatial\Tests\Fixtures\GeographyEntity;
use Bcremer\Spatial\Tests\OrmTestCase;
use TypeError;

/**
 * Doctrine GeographyType tests
 *
 * @author  Derek J. Lambert <dlambert@dereklambert.com>
 * @license http://dlambert.mit-license.org MIT
 *
 * @group geography
 */
class GeographyTypeTest extends OrmTestCase
{
    protected function setUp(): void
    {
        $this->usesEntity(self::GEOGRAPHY_ENTITY);
        parent::setUp();
    }

    public function testNullGeography(): void
    {
        $entity = new GeographyEntity();

        $this->getEntityManager()->persist($entity);
        $this->getEntityManager()->flush();

        $id = $entity->getId();

        $this->getEntityManager()->clear();

        $queryEntity = $this->getEntityManager()->getRepository(self::GEOGRAPHY_ENTITY)->find($id);

        $this->assertEquals($entity, $queryEntity);
    }

    public function testPointGeography(): void
    {
        $entity = new GeographyEntity();

        $entity->setGeography(new Point(1, 1));
        $this->getEntityManager()->persist($entity);
        $this->getEntityManager()->flush();

        $id = $entity->getId();

        $this->getEntityManager()->clear();

        $queryEntity = $this->getEntityManager()->getRepository(self::GEOGRAPHY_ENTITY)->find($id);

        $this->assertEquals($entity, $queryEntity);
    }

    public function testLineStringGeography(): void
    {
        $entity = new GeographyEntity();

        $entity->setGeography(new LineString(
            [
                 new Point(0, 0),
                 new Point(1, 1)
                                  ]
        ));
        $this->getEntityManager()->persist($entity);
        $this->getEntityManager()->flush();

        $id = $entity->getId();

        $this->getEntityManager()->clear();

        $queryEntity = $this->getEntityManager()->getRepository(self::GEOGRAPHY_ENTITY)->find($id);

        $this->assertEquals($entity, $queryEntity);
    }

    public function testPolygonGeography(): void
    {
        $entity = new GeographyEntity();

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

        $entity->setGeography(new Polygon($rings));
        $this->getEntityManager()->persist($entity);
        $this->getEntityManager()->flush();

        $id = $entity->getId();

        $this->getEntityManager()->clear();

        $queryEntity = $this->getEntityManager()->getRepository(self::GEOGRAPHY_ENTITY)->find($id);

        $this->assertEquals($entity, $queryEntity);
    }

    public function testBadGeographyValue(): void
    {
        $this->expectException(TypeError::class);

        $entity = new GeographyEntity();
        $entity->setGeography('POINT(0 0)');
    }
}
