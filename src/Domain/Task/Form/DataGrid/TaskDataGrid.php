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

namespace App\Domain\Task\Form\DataGrid;

use App\Domain\User\Entity\User;
use App\Domain\Work\DTO\Repository\WorkRepositoryDTO;
use Doctrine\ORM\QueryBuilder;
use App\Infrastructure\Interfaces\Bus\QueryBusInterface;
use App\Domain\Work\Bus\Query\WorkQueryBuilderBySupervisor\{GetWorkQueryBuilderBySupervisorQuery, GetWorkQueryBuilderBySupervisorQueryResult};

class TaskDataGrid
{
    public function __construct(private readonly QueryBusInterface $queryBus) {}

    public function queryBuilderWorksBySupervisor(
        User $user,
        ?array $workStatus = null
    ): QueryBuilder {
        $workData = new WorkRepositoryDTO(
            supervisor: $user,
            workStatus: $workStatus
        );

        $getWorkQueryBuilderBySupervisorQuery = GetWorkQueryBuilderBySupervisorQuery::create($workData);
        /** @var GetWorkQueryBuilderBySupervisorQueryResult $getWorkQueryBuilderBySupervisorQueryResult */
        $getWorkQueryBuilderBySupervisorQueryResult = $this->queryBus->handle($getWorkQueryBuilderBySupervisorQuery);
        $queryBuilder = $getWorkQueryBuilderBySupervisorQueryResult->queryBuilder;

        return $queryBuilder;
    }
}
