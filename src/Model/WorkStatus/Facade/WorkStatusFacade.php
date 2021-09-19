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

namespace App\Model\WorkStatus\Facade;

use App\DataTransferObject\Repository\WorkStatusData;
use App\Entity\WorkStatus;
use App\Repository\WorkStatusRepository;

class WorkStatusFacade
{
    public function __construct(private WorkStatusRepository $workStatusRepository)
    {
    }

    public function find(int $id): ?WorkStatus
    {
        return $this->workStatusRepository
            ->find($id);
    }

    public function findAll(int $limit = null): array
    {
        return $this->workStatusRepository
            ->baseQueryBuilder()
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function getCountByUser(WorkStatusData $workStatusData): array
    {
        return $this->workStatusRepository
            ->countByUser($workStatusData)
            ->getQuery()
            ->getResult();
    }
}
