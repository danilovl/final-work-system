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

namespace App\Domain\Work\Facade;

use App\Domain\Work\DataTransferObject\WorkRepositoryData;
use App\Domain\Work\Entity\Work;
use App\Domain\Work\Repository\WorkRepository;
use Doctrine\ORM\{
    Query,
    QueryBuilder
};
use Webmozart\Assert\Assert;

readonly class WorkFacade
{
    public function __construct(private WorkRepository $workRepository) {}

    public function find(int $id): ?Work
    {
        /** @var Work|null $result */
        $result = $this->workRepository->find($id);

        return $result;
    }

    /**
     * @return Work[]
     */
    public function findAll(?int $limit = null): array
    {
        /** @var array $result */
        $result = $this->workRepository
            ->baseQueryBuilder()
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();

        Assert::allIsInstanceOf($result, Work::class);

        return $result;
    }

    public function getQueryBuilderWorksBySupervisor(WorkRepositoryData $workData): QueryBuilder
    {
        return $this->workRepository->allByUserStatus($workData);
    }

    /**
     * @return Work[]
     */
    public function getWorksByAuthorSupervisorStatus(WorkRepositoryData $workData): array
    {
        /** @var array $result */
        $result = $this->workRepository
            ->allByUserStatus($workData)
            ->getQuery()
            ->getResult();

        Assert::allIsInstanceOf($result, Work::class);

        return $result;
    }

    /**
     * @return Work[]
     */
    public function getWorksByAuthorStatus(WorkRepositoryData $workData): array
    {
        /** @var array $result */
        $result = $this->workRepository
            ->allByUserStatus($workData)
            ->getQuery()
            ->getResult();

        Assert::allIsInstanceOf($result, Work::class);

        return $result;
    }

    public function queryAllByUserStatus(WorkRepositoryData $workData): Query
    {
        return $this->workRepository
            ->allByUserStatus($workData)
            ->getQuery();
    }
}
