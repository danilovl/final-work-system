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

use App\Infrastructure\Exception\EntityNotFoundException;
use App\Domain\User\Entity\User;
use App\Domain\WorkStatus\Entity\WorkStatus;
use App\Domain\WorkStatus\Repository\WorkStatusRepository;
use Doctrine\ORM\QueryBuilder;

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
    /**
     * @param int[] $userIds
     * @param WorkStatus[]|null $workStatus
     * @return array<int, array<array{name: string, count: int|string}>>
     */
    public function listCountByUsers(
        array $userIds,
        User $supervisor,
        string $type,
        ?array $workStatus
    ): array {
        if (count($userIds) === 0) {
            return [];
        }

        /** @var array<array{userId: int|string, name: string, count: int|string}> $rows */
        $rows = $this->workStatusRepository
            ->countByUsers($userIds, $supervisor, $type, $workStatus)
            ->getQuery()
            ->getResult();

        $result = [];
        foreach ($rows as $row) {
            $userId = (int) $row['userId'];
            unset($row['userId']);

            $result[$userId][] = $row;
        }

        return $result;
    }

    public function queryBuilderAllOrderedByName(): QueryBuilder
    {
        return $this->workStatusRepository->allOrderedByName();
    }
}
