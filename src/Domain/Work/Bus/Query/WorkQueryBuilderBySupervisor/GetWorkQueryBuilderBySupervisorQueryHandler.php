<?php declare(strict_types=1);

/**
 *
 * This file is part of the FinalWorkSystem project.
 * (c) Vladimir Danilov
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace App\Domain\Work\Bus\Query\WorkQueryBuilderBySupervisor;

use App\Application\Interfaces\Bus\QueryHandlerInterface;
use App\Domain\Work\Facade\WorkFacade;

readonly class GetWorkQueryBuilderBySupervisorQueryHandler implements QueryHandlerInterface
{
    public function __construct(private WorkFacade $workFacade) {}

    public function __invoke(GetWorkQueryBuilderBySupervisorQuery $query): GetWorkQueryBuilderBySupervisorQueryResult
    {
        $queryBuilder = $this->workFacade->queryBuilderBySupervisor($query->workRepositoryDTO);

        return new GetWorkQueryBuilderBySupervisorQueryResult($queryBuilder);
    }
}
