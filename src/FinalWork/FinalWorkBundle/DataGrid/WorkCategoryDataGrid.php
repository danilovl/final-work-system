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

namespace FinalWork\FinalWorkBundle\DataGrid;

use Doctrine\ORM\{
    QueryBuilder,
    EntityManager
};
use FinalWork\FinalWorkBundle\Entity\Repository\WorkCategoryRepository;
use FinalWork\FinalWorkBundle\Entity\WorkCategory;
use FinalWork\SonataUserBundle\Entity\User;

class WorkCategoryDataGrid
{
    /**
     * @var WorkCategoryRepository
     */
    private $workCategoryRepository;

    /**
     * WorkDataGrid constructor.
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->workCategoryRepository = $entityManager->getRepository(WorkCategory::class);
    }

    /**
     * @param User $user
     * @return QueryBuilder
     */
    public function queryBuilderWorkCategoriesByOwner(User $user): QueryBuilder
    {
        return $this->workCategoryRepository->findAllByOwner($user);
    }
}
