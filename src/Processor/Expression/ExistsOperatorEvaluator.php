<?php
namespace Vimeo\MysqlEngine\Processor\Expression;

use Vimeo\MysqlEngine\Parser\ParserException;
use Vimeo\MysqlEngine\Query\Expression\ExistsOperatorExpression;
use Vimeo\MysqlEngine\Query\Expression\SubqueryExpression;
use Vimeo\MysqlEngine\Processor\QueryResult;
use Vimeo\MysqlEngine\Processor\Scope;
use Vimeo\MysqlEngine\Schema\Column;

final class ExistsOperatorEvaluator
{
    /**
     * @param array<string, mixed> $row
     * @param array<string, Column> $columns
     *
     * @return mixed
     */
    public static function evaluate(
        \Vimeo\MysqlEngine\FakePdo $conn,
        Scope $scope,
        ExistsOperatorExpression $expr,
        array $row,
        QueryResult $result
    ) {
        if (!$expr->isWellFormed()) {
            throw new ParserException("Parse error: empty EXISTS subquery");
        }

        if ($expr->exists instanceof SubqueryExpression) {
            $ret = \Vimeo\MysqlEngine\Processor\SelectProcessor::process(
                $conn,
                $scope,
                $expr->exists->query,
                $row,
                $result->columns
            )->rows;
        } else {
            $ret = Evaluator::evaluate($conn, $scope, $expr->exists, $row, $result);
        }

        if ($expr->negated) {
            return $ret ? 0 : 1;
        }

        return $ret ? 1 : 0;
    }
}
