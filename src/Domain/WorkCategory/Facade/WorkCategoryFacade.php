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

namespace App\Domain\WorkCategory\Facade;

use App\Application\Exception\EntityNotFoundException;
use App\Domain\WorkCategory\Entity\WorkCategory;
use App\Domain\WorkCategory\Repository\WorkCategoryRepository;
use Doctrine\ORM\Query;
use App\Domain\User\Entity\User;

readonly class WorkCategoryFacade
{
    public function __construct(private WorkCategoryRepository $workCategoryRepository) {}
    
    public function findById(int $id): ?WorkCategory
    {
        /** @var WorkCategory|null $result */
        $result = $this->workCategoryRepository->find($id);

        return $result;
    }

    public function getById(int $id): WorkCategory
    {
        $workCategory = $this->findById($id);
        if ($workCategory === null) {
            throw new EntityNotFoundException("WorkCategory with id {$id} not found.");
        }

        return $workCategory;
    }

    /**
     * @return WorkCategory[]
     */
    public function findByIds(array $ids): array
    {
        /** @var WorkCategory[] $result */
        $result = $this->workCategoryRepository->findBy(['id' => $ids]);

        return $result;
    }

    /**
     * @return WorkCategory[]
     */
    public function getByIds(array $ids): array
    {
        $categories = $this->findByIds($ids);
        if (count($categories) !== count($ids)) {
            throw new EntityNotFoundException('One or more WorkCategories not found.');
        }

        return $categories;
    }

    public function queryByOwner(User $user): Query
    {
        return $this->workCategoryRepository
            ->allByOwner($user)
            ->getQuery();
    }

    /**
     * @return WorkCategory[]
     */
    public function getAllByOwner(User $user): array
    {
        /** @var WorkCategory[] $result */
        $result = $this->queryByOwner($user)->getResult();

        return $result;
    }

    /**
     * @param int[] $ids
     */
    public function countByOwnerAndIds(User $user, array $ids): int
    {
        return $this->workCategoryRepository->countByOwnerAndIds($user, $ids);
    }

    /**
     * @param int[] $ids
     * @return WorkCategory[]
     */
    public function findByOwnerAndIds(User $user, array $ids): array
    {
        return $this->workCategoryRepository->findByOwnerAndIds($user, $ids);
    }
}
