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

namespace App\Domain\WorkStatus\Facade;

use App\Application\Exception\EntityNotFoundException;
use App\Domain\WorkStatus\DTO\Repository\WorkStatusRepositoryDTO;
use App\Domain\WorkStatus\Entity\WorkStatus;
use App\Domain\WorkStatus\Repository\WorkStatusRepository;

readonly class WorkStatusFacade
{
    public function __construct(private WorkStatusRepository $workStatusRepository) {}

    public function findById(int $id): ?WorkStatus
    {
        /** @var WorkStatus|null $result */
        $result = $this->workStatusRepository->find($id);

        return $result;
    }

    public function getById(int $id): WorkStatus
    {
        $status = $this->findById($id);
        if ($status === null) {
            throw new EntityNotFoundException("WorkStatus with id {$id} not found.");
        }

        return $status;
    }

    /**
     * @return WorkStatus[]
     */
    public function list(?int $limit = null): array
    {
        /** @var WorkStatus[] $result */
        $result = $this->workStatusRepository
            ->baseQueryBuilder()
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();

        return $result;
    }

    /**
     * @return WorkStatus[]
     */
    public function getAll(): array
    {
        return $this->list();
    }

    /**
     * @return array<string, int>
     */
    public function listCountByUser(WorkStatusRepositoryDTO $workStatusData): array
    {
        /** @var array<string, int> $result */
        $result = $this->workStatusRepository
            ->countByUser($workStatusData)
            ->getQuery()
            ->getResult();

        return $result;
    }
}
