<?php

namespace CrEOF\Spatial\ORM\Query\AST\Functions\MySql;

use CrEOF\Spatial\ORM\Query\AST\Functions\AbstractSpatialDQLFunction;

/**
 * Description of STContains
 *
 * @author Maximilian
 */
class GeodistPt extends AbstractSpatialDQLFunction
{


    protected string $functionName = 'geodist_pt';

    protected ?int $minGeomExpr = 2;

    protected ?int $maxGeomExpr = 2;
}
