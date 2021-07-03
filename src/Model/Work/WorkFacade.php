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

namespace App\Model\Work;

use App\DataTransferObject\Repository\WorkData;
use App\Entity\{
    User,
    Work
};
use App\Repository\WorkRepository;

class WorkFacade
{
    public function __construct(private WorkRepository $workRepository)
    {
    }

    public function find(int $id): ?Work
    {
        return $this->workRepository->find($id);
    }

    /**
     * @return Work[]
     */
    public function findAll(?int $limit = null): array
    {
        return $this->workRepository
            ->baseQueryBuilder()
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Work[]
     */
    public function getWorksByAuthorSupervisorStatus(WorkData $workData): array
    {
        return $this->workRepository
            ->allByUserStatus($workData)
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Work[]
     */
    public function getWorksByAuthorStatus(WorkData $workData): array
    {
        return $this->workRepository
            ->allByUserStatus($workData)
            ->getQuery()
            ->getResult();
    }
}
