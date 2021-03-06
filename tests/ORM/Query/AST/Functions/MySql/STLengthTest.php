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
use Bcremer\Spatial\Tests\Fixtures\LineStringEntity;
use Bcremer\Spatial\Tests\OrmTestCase;

/**
 * STLength DQL function tests
 *
 * @author  Derek J. Lambert <dlambert@dereklambert.com>
 * @license http://dlambert.mit-license.org MIT
 *
 * @group dql
 */
class STLengthTest extends OrmTestCase
{
    protected function setUp(): void
    {
        $this->usesEntity(self::LINESTRING_ENTITY);

        parent::setUp();
    }

    /**
     * @group geometry
     */
    public function testSelectGLength(): void
    {
        $entity = new LineStringEntity();

        $entity->setLineString(new LineString(
            [
            new Point(0, 0),
            new Point(1, 1),
            new Point(2, 2)
                                   ]
        ));

        $this->getEntityManager()->persist($entity);
        $this->getEntityManager()->flush();
        $this->getEntityManager()->clear();

        $query  = $this->getEntityManager()->createQuery('SELECT l, ST_Length(l.lineString) FROM Bcremer\Spatial\Tests\Fixtures\LineStringEntity l');
        $result = $query->getResult();

        $this->assertCount(1, $result);
        $this->assertEquals($entity, $result[0][0]);
        $this->assertEquals(2.82842712474619, $result[0][1]);
    }

    /**
     * @group geometry
     */
    public function testGLengthWhereParameter(): void
    {
        $entity = new LineStringEntity();

        $entity->setLineString(new LineString(
            [
            new Point(0, 0),
            new Point(1, 1),
            new Point(2, 2)
                                   ]
        ));

        $this->getEntityManager()->persist($entity);
        $this->getEntityManager()->flush();
        $this->getEntityManager()->clear();

        $query  = $this->getEntityManager()->createQuery('SELECT l FROM Bcremer\Spatial\Tests\Fixtures\LineStringEntity l WHERE ST_Length(ST_GeomFromText(:p1)) > ST_Length(l.lineString)');

        $query->setParameter('p1', 'LINESTRING(0 0,1 1,2 2,3 3,4 4,5 5)', 'string');

        $result = $query->getResult();

        $this->assertCount(1, $result);
        $this->assertEquals($entity, $result[0]);
    }
}
