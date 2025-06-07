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

use App\Domain\WorkStatus\DataTransferObject\WorkStatusRepositoryData;
use App\Domain\WorkStatus\Entity\WorkStatus;
use App\Domain\WorkStatus\Repository\WorkStatusRepository;
use Webmozart\Assert\Assert;

readonly class WorkStatusFacade
{
    public function __construct(private WorkStatusRepository $workStatusRepository) {}

    public function find(int $id): ?WorkStatus
    {
        /** @var WorkStatus|null $result */
        $result = $this->workStatusRepository->find($id);

        return $result;
    }

    /**
     * @return WorkStatus[]
     */
    public function findAll(?int $limit = null): array
    {
        /** @var array $result */
        $result = $this->workStatusRepository
            ->baseQueryBuilder()
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();

        Assert::allIsInstanceOf($result, WorkStatus::class);

        return $result;
    }

    /**
     * @return array<string, int>
     */
    public function getCountByUser(WorkStatusRepositoryData $workStatusData): array
    {
        /** @var array<string, int> $result */
        $result = $this->workStatusRepository
            ->countByUser($workStatusData)
            ->getQuery()
            ->getResult();

        return $result;
    }
}
