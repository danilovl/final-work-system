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

namespace App\Model\WorkStatus;

use App\Entity\WorkStatus;
use App\Repository\WorkStatusRepository;
use App\Entity\User;

class WorkStatusFacade
{
    private WorkStatusRepository $workStatusRepository;

    public function __construct(WorkStatusRepository $workStatusRepository)
    {
        $this->workStatusRepository = $workStatusRepository;
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

    public function getCountByUser(
        User $user,
        User $supervisor,
        string $type,
        $workStatus
    ): array {
        return $this->workStatusRepository
            ->countByUser($user, $supervisor, $type, $workStatus)
            ->getQuery()
            ->getResult();
    }
}
