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

use InvalidArgumentException;
use App\Application\Exception\EntityNotFoundException;
use App\Domain\User\Entity\User;
use App\Domain\Work\Constant\WorkUserTypeConstant;
use App\Domain\Work\DTO\Repository\WorkRepositoryDTO;
use App\Domain\Work\Entity\Work;
use App\Domain\Work\Repository\WorkRepository;
use App\Domain\WorkStatus\Entity\WorkStatus;
use App\Domain\WorkType\Entity\WorkType;
use Doctrine\ORM\{
    Query,
    QueryBuilder
};

readonly class WorkFacade
{
    public function __construct(private WorkRepository $workRepository) {}

    public function findById(int $id): ?Work
    {
        /** @var Work|null $result */
        $result = $this->workRepository->find($id);

        return $result;
    }

    public function getById(int $id): Work
    {
        $work = $this->findById($id);
        if ($work === null) {
            throw new EntityNotFoundException("Work with id {$id} not found.");
        }

        return $work;
    }

    /**
     * @return Work[]
     */
    public function list(?int $limit = null): array
    {
        /** @var Work[] $result */
        $result = $this->workRepository
            ->baseQueryBuilder()
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();

        return $result;
    }

    public function queryBuilderBySupervisor(WorkRepositoryDTO $workRepositoryDTO): QueryBuilder
    {
        return $this->workRepository->allByUserStatus($workRepositoryDTO);
    }

    /**
     * @param int[] $userIds
     * @param WorkStatus[]|null $workStatus
     * @return array<int, Work[]>
     */
    public function listByUsersSupervisorStatus(
        array $userIds,
        User $supervisor,
        string $type,
        ?array $workStatus
    ): array {
        if (count($userIds) === 0) {
            return [];
        }

        /** @var Work[] $works */
        $works = $this->workRepository
            ->allByUsersSupervisorStatus($userIds, $supervisor, $type, $workStatus)
            ->getQuery()
            ->getResult();

        $result = [];
        foreach ($works as $work) {
            $workUser = match ($type) {
                WorkUserTypeConstant::AUTHOR->value => $work->getAuthor(),
                WorkUserTypeConstant::OPPONENT->value => $work->getOpponent(),
                WorkUserTypeConstant::CONSULTANT->value => $work->getConsultant(),
                default => throw new InvalidArgumentException('Unsupported work user type.')
            };

            if ($workUser === null) {
                continue;
            }

            $result[$workUser->getId()][] = $work;
        }

        return $result;
    }

    /**
     * @return Work[]
     */
    public function listByUserRole(
        User $user,
        string $type,
        ?WorkType $workType = null,
        ?WorkStatus $workStatus = null
    ): array {
        /** @var Work[] $result */
        $result = $this->workRepository
            ->allByUserRole(
                user: $user,
                type: $type,
                workType: $workType,
                workStatus: $workStatus
            )
            ->getQuery()
            ->getResult();

        return $result;
    }

    /**
     * @return Work[]
     */
    public function listByAuthorStatus(WorkRepositoryDTO $workData): array
    {
        /** @var Work[] $result */
        $result = $this->workRepository
            ->allByUserStatus($workData)
            ->getQuery()
            ->getResult();

        return $result;
    }

    public function queryByUserStatus(WorkRepositoryDTO $workData): Query
    {
        return $this->workRepository
            ->allByUserStatus($workData)
            ->getQuery();
    }

    public function queryBuilderDeadlineBySupervisor(User $user): QueryBuilder
    {
        return $this->workRepository->workDeadlineBySupervisor($user);
    }
}
