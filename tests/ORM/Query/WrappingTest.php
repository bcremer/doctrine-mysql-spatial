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

namespace CrEOF\Spatial\Tests\ORM\Query;

use CrEOF\Spatial\PHP\Types\Geometry\LineString;
use CrEOF\Spatial\PHP\Types\Geometry\Point;
use CrEOF\Spatial\PHP\Types\Geometry\Polygon;
use CrEOF\Spatial\Tests\Fixtures\GeometryEntity;
use CrEOF\Spatial\Tests\Fixtures\PolygonEntity;
use CrEOF\Spatial\Tests\OrmTestCase;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\Version;

/**
 * DQL type wrapping tests
 *
 * @author  Derek J. Lambert <dlambert@dereklambert.com>
 * @license http://dlambert.mit-license.org MIT
 *
 * @group dql
 */
class WrappingTest extends OrmTestCase
{
    protected function setUp(): void
    {
        $this->usesEntity(self::GEOMETRY_ENTITY);
        $this->usesType('point');
        parent::setUp();
    }

    /**
     * @group geometry
     */
    public function testTypeWrappingSelect(): void
    {
        $lineString = new LineString(
            [
            new Point(0, 0),
            new Point(10, 0),
            new Point(10, 10),
            new Point(0, 10),
            new Point(0, 0)
            ]
        );
        $entity = new PolygonEntity();

        $entity->setPolygon(new Polygon([$lineString]));
        $this->getEntityManager()->persist($entity);
        $this->getEntityManager()->flush();
        $this->getEntityManager()->clear();

        $dql = 'SELECT p, ST_Contains(p.polygon, :geometry) FROM CrEOF\Spatial\Tests\Fixtures\PolygonEntity p';

        $query = $this->getEntityManager()->createQuery($dql);

        $query->setParameter('geometry', new Point(2, 2), 'point');
        $query->processParameterValue('geometry');

        $result    = $query->getSQL();
        $parameter = '?';
        $parameter = Type::getType('point')->convertToDatabaseValueSQL($parameter, $this->getPlatform());

        $regex = sprintf('/(.polygon, %s)/', preg_quote($parameter, '/'));

        $this->assertMatchesRegularExpression($regex, $result);
    }

    /**
     * @group geometry
     */
    public function testTypeWrappingWhere(): void
    {
        $entity = new GeometryEntity();

        $entity->setGeometry(new Point(5, 5));
        $this->getEntityManager()->persist($entity);
        $this->getEntityManager()->flush();
        $this->getEntityManager()->clear();

        $query = $this->getEntityManager()->createQuery('SELECT g FROM CrEOF\Spatial\Tests\Fixtures\GeometryEntity g WHERE g.geometry = :geometry');

        $query->setParameter('geometry', new Point(5, 5), 'point');
        $query->processParameterValue('geometry');

        $result    = $query->getSQL();
        $parameter = '?';

        if (Version::compare('2.5') <= 0) {
            $parameter = Type::getType('point')->convertToDatabaseValueSQL($parameter, $this->getPlatform());
        }

        $regex = sprintf('/geometry = %s/', preg_quote($parameter, '/'));


        $this->assertMatchesRegularExpression($regex, $result);
    }
}
