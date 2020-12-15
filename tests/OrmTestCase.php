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

namespace CrEOF\Spatial\Tests;

use CrEOF\Spatial\DBAL\Types\Geography\LineStringType;
use CrEOF\Spatial\DBAL\Types\Geography\PointType;
use CrEOF\Spatial\DBAL\Types\Geography\PolygonType;
use CrEOF\Spatial\DBAL\Types\GeographyType;
use CrEOF\Spatial\DBAL\Types\Geometry\MultiPolygonType;
use CrEOF\Spatial\DBAL\Types\GeometryType;
use CrEOF\Spatial\Exception\UnsupportedPlatformException;
use CrEOF\Spatial\ORM\Query\AST\Functions\MySql\MBRContains;
use CrEOF\Spatial\ORM\Query\AST\Functions\MySql\MBRDisjoint;
use CrEOF\Spatial\ORM\Query\AST\Functions\MySql\STArea;
use CrEOF\Spatial\ORM\Query\AST\Functions\MySql\STAsBinary;
use CrEOF\Spatial\ORM\Query\AST\Functions\MySql\STAsText;
use CrEOF\Spatial\ORM\Query\AST\Functions\MySql\STContains;
use CrEOF\Spatial\ORM\Query\AST\Functions\MySql\STDisjoint;
use CrEOF\Spatial\ORM\Query\AST\Functions\MySql\STEnvelope;
use CrEOF\Spatial\ORM\Query\AST\Functions\MySql\STGeomFromText;
use CrEOF\Spatial\ORM\Query\AST\Functions\MySql\STLength;
use CrEOF\Spatial\ORM\Query\AST\Functions\MySql\STStartPoint;
use CrEOF\Spatial\Tests\Fixtures\GeographyEntity;
use CrEOF\Spatial\Tests\Fixtures\GeoLineStringEntity;
use CrEOF\Spatial\Tests\Fixtures\GeometryEntity;
use CrEOF\Spatial\Tests\Fixtures\GeoPolygonEntity;
use CrEOF\Spatial\Tests\Fixtures\LineStringEntity;
use CrEOF\Spatial\Tests\Fixtures\MultiPolygonEntity;
use CrEOF\Spatial\Tests\Fixtures\NoHintGeometryEntity;
use CrEOF\Spatial\Tests\Fixtures\PointEntity;
use CrEOF\Spatial\Tests\Fixtures\PolygonEntity;
use Doctrine\Common\Cache\ArrayCache;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Logging\DebugStack;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\SchemaTool;
use Exception;
use PHPUnit\Framework\TestCase;
use Throwable;

/**
 * Abstract ORM test class
 *
 * @author  Derek J. Lambert <dlambert@dereklambert.com>
 * @license http://dlambert.mit-license.org MIT
 */
abstract class OrmTestCase extends TestCase
{
    public const GEOMETRY_ENTITY = GeometryEntity::class;
    public const NO_HINT_GEOMETRY_ENTITY = NoHintGeometryEntity::class;
    public const POINT_ENTITY = PointEntity::class;
    public const LINESTRING_ENTITY = LineStringEntity::class;
    public const POLYGON_ENTITY = PolygonEntity::class;
    public const MULTIPOLYGON_ENTITY = MultiPolygonEntity::class;
    public const GEOGRAPHY_ENTITY = GeographyEntity::class;
    public const GEO_LINESTRING_ENTITY = GeoLineStringEntity::class;
    public const GEO_POLYGON_ENTITY = GeoPolygonEntity::class;

    /**
     * @var bool[]
     */
    private array $usedTypes = [];

    /**
     * @var bool[]
     */
    private array $usedEntities = [];

    /**
     * @var bool[]
     */
    private static array $createdEntities = [];

    /**
     * @var bool[]
     */
    private static array $addedTypes = [];

    private static Connection $connection;

    private EntityManager $entityManager;

    private SchemaTool $schemaTool;

    private DebugStack $sqlLoggerStack;

    private static array $entities = [
        self::GEOMETRY_ENTITY => [
            'types' => ['geometry'],
            'table' => 'GeometryEntity',
        ],
        self::NO_HINT_GEOMETRY_ENTITY => [
            'types' => ['geometry'],
            'table' => 'NoHintGeometryEntity',
        ],
        self::POINT_ENTITY => [
            'types' => ['point'],
            'table' => 'PointEntity',
        ],
        self::LINESTRING_ENTITY => [
            'types' => ['linestring'],
            'table' => 'LineStringEntity',
        ],
        self::POLYGON_ENTITY => [
            'types' => ['polygon'],
            'table' => 'PolygonEntity',
        ],
        self::MULTIPOLYGON_ENTITY => [
            'types' => ['multipolygon'],
            'table' => 'MultiPolygonEntity',
        ],
        self::GEOGRAPHY_ENTITY => [
            'types' => ['geography'],
            'table' => 'GeographyEntity',
        ],
        self::GEO_LINESTRING_ENTITY => [
            'types' => ['geolinestring'],
            'table' => 'GeoLineStringEntity',
        ],
        self::GEO_POLYGON_ENTITY => [
            'types' => ['geopolygon'],
            'table' => 'GeoPolygonEntity',
        ],
    ];

    private static array $types = [
        'geometry' => GeometryType::class,
        'point' => \CrEOF\Spatial\DBAL\Types\Geometry\PointType::class,
        'linestring' => \CrEOF\Spatial\DBAL\Types\Geometry\LineStringType::class,
        'polygon' => \CrEOF\Spatial\DBAL\Types\Geometry\PolygonType::class,
        'multipolygon' => MultiPolygonType::class,
        'geography' => GeographyType::class,
        'geopoint' => PointType::class,
        'geolinestring' => LineStringType::class,
        'geopolygon' => PolygonType::class,
    ];


    /**
     * @throws UnsupportedPlatformException
     */
    public static function setUpBeforeClass(): void
    {
        static::$connection = static::getConnection();
    }

    /**
     * Creates a connection to the test database, if there is none yet, and
     * creates the necessary tables.
     *
     * @throws UnsupportedPlatformException
     */
    protected function setUp(): void
    {
        $this->entityManager = $this->getEntityManager();
        $this->schemaTool = $this->getSchemaTool();

        $this->setUpTypes();
        $this->setUpEntities();
        $this->setUpFunctions();
    }

    protected function getEntityManager(): EntityManager
    {
        if (isset($this->entityManager)) {
            return $this->entityManager;
        }

        $this->sqlLoggerStack = new DebugStack();
        $this->sqlLoggerStack->enabled = true;

        static::getConnection()->getConfiguration()->setSQLLogger($this->sqlLoggerStack);

        $config = new Configuration();

        $config->setMetadataCacheImpl(new ArrayCache());
        $config->setProxyDir(__DIR__ . '/Proxies');
        $config->setProxyNamespace('CrEOF\Spatial\Tests\Proxies');
        $config->setMetadataDriverImpl($config->newDefaultAnnotationDriver([realpath(__DIR__ . '/Fixtures')], true));

        return EntityManager::create(static::getConnection(), $config);
    }

    protected function getSchemaTool(): SchemaTool
    {
        if (isset($this->schemaTool)) {
            return $this->schemaTool;
        }

        return new SchemaTool($this->getEntityManager());
    }

    protected function usesType(string $typeName): void
    {
        $this->usedTypes[$typeName] = true;
    }

    protected function usesEntity(string $entityClass): void
    {
        $this->usedEntities[$entityClass] = true;

        foreach (static::$entities[$entityClass]['types'] as $type) {
            $this->usesType($type);
        }
    }

    /**
     * @return array
     */
    protected function getUsedEntityClasses(): array
    {
        return static::$createdEntities;
    }

    /**
     * Add types used by test to DBAL
     */
    protected function setUpTypes(): void
    {
        foreach (array_keys($this->usedTypes) as $typeName) {
            if (!isset(static::$addedTypes[$typeName]) && !Type::hasType($typeName)) {
                Type::addType($typeName, static::$types[$typeName]);

                $type = Type::getType($typeName);

                // Since doctrineTypeComments may already be initialized check if added type requires comment
                if ($type->requiresSQLCommentHint($this->getPlatform()) && !$this->getPlatform()->isCommentedDoctrineType($type)) {
                    $this->getPlatform()->markDoctrineTypeCommented(Type::getType($typeName));
                }

                static::$addedTypes[$typeName] = true;
            }
        }
    }

    /**
     * Create entities used by tests
     */
    protected function setUpEntities(): void
    {
        $classes = [];

        foreach (array_keys($this->usedEntities) as $entityClass) {
            if (!isset(static::$createdEntities[$entityClass])) {
                static::$createdEntities[$entityClass] = true;
                $classes[] = $this->getEntityManager()->getClassMetadata($entityClass);
            }
        }

        if ($classes) {
            $this->getSchemaTool()->createSchema($classes);
        }
    }

    /**
     * Setup DQL functions
     */
    protected function setUpFunctions(): void
    {
        $configuration = $this->getEntityManager()->getConfiguration();

        $configuration->addCustomNumericFunction('st_area', STArea::class);
        $configuration->addCustomStringFunction('st_asbinary', STAsBinary::class);
        $configuration->addCustomStringFunction('st_astext', STAsText::class);
        $configuration->addCustomNumericFunction('st_contains', STContains::class);
        $configuration->addCustomNumericFunction('st_disjoint', STDisjoint::class);
        $configuration->addCustomStringFunction('st_envelope', STEnvelope::class);
        $configuration->addCustomStringFunction('st_geomfromtext', STGeomFromText::class);
        $configuration->addCustomNumericFunction('st_length', STLength::class);
        $configuration->addCustomNumericFunction('mbrcontains', MBRContains::class);
        $configuration->addCustomNumericFunction('mbrdisjoint', MBRDisjoint::class);
        $configuration->addCustomStringFunction('st_startpoint', STStartPoint::class);
    }

    /**
     * Teardown fixtures
     */
    protected function tearDown(): void
    {
        $this->sqlLoggerStack->enabled = false;

        foreach (array_keys($this->usedEntities) as $entityName) {
            static::getConnection()->executeStatement(sprintf('DELETE FROM %s', static::$entities[$entityName]['table']));
        }

        $this->getEntityManager()->clear();
    }

    protected function getPlatform(): AbstractPlatform
    {
        return static::getConnection()->getDatabasePlatform();
    }

    protected function onNotSuccessfulTest(Throwable $e): void
    {
        $queryCount = count($this->sqlLoggerStack->queries);
        if ($queryCount === 0) {
            throw $e;
        }

        $queries = array_map(
            static function (array $query) use (&$queryCount): string {
                $params = array_map(
                    static fn($param) => is_object($param) ? get_class($param) : sprintf("'%s'", $param),
                    $query['params'] ?: []
                );

                return sprintf("%2d. SQL: '%s' Params: %s", $queryCount--, $query['sql'], implode(", ", $params));
            },
            array_slice(array_reverse($this->sqlLoggerStack->queries), 0, 25)
        );

        $traces = array_filter(
            $e->getTrace(),
            static fn(array $frame) => strpos($frame['file'], "phpunit/") === false
        );

        $traces = array_map(
            static fn(array $frame) => sprintf("%s:%s", $frame['file'], $frame['line']),
            $traces
        );

        $message = sprintf("[%s] %s\n\n", get_class($e), $e->getMessage());
        $message .= sprintf("With queries:\n%s\nTrace:\n%s", implode("\n", $queries), implode("\n", $traces));

        throw new Exception($message, (int)$e->getCode(), $e);
    }

    /**
     * Using the SQL Logger Stack this method retrieves the current query count executed in this test.
     */
    protected function getCurrentQueryCount(): int
    {
        return count($this->sqlLoggerStack->queries);
    }

    /**
     * @throws UnsupportedPlatformException
     * @throws DBALException
     */
    protected static function getConnection(): Connection
    {
        if (isset(static::$connection)) {
            return static::$connection;
        }

        $parameters = static::getConnectionParameters();

        $tmpParameters = $parameters;
        unset($tmpParameters['dbname']);

        $tmpConnection = DriverManager::getConnection($tmpParameters);
        $tmpConnection->getSchemaManager()->dropAndCreateDatabase($parameters['dbname']);
        $tmpConnection->close();

        $connection = DriverManager::getConnection($parameters);

        if ($connection->getDatabasePlatform()->getName() !== 'mysql') {
            throw new UnsupportedPlatformException(
                sprintf('DBAL platform "%s" is not currently supported.', $connection->getDatabasePlatform()->getName())
            );
        }

        return static::$connection = $connection;
    }

    private static function getConnectionParameters(): array
    {
        $connectionParams = [
            'driver' => $_ENV['db_type'],
            'user' => $_ENV['db_username'],
            'password' => $_ENV['db_password'],
            'host' => $_ENV['db_host'],
            'dbname' => $_ENV['db_name'],
            'port' => $_ENV['db_port'],
        ];

        if (isset($_ENV['db_unix_socket'])) {
            $connectionParams['unix_socket'] = $_ENV['db_unix_socket'];
        }

        return $connectionParams;
    }
}
