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

namespace FinalWork\FinalWorkBundle\Model\WorkCategory;

use Doctrine\ORM\{
    Query,
    EntityManager
};
use FinalWork\FinalWorkBundle\Entity\Repository\WorkCategoryRepository;
use FinalWork\FinalWorkBundle\Entity\WorkCategory;
use FinalWork\SonataUserBundle\Entity\User;

class WorkCategoryFacade
{
    /**
     * @var WorkCategoryRepository
     */
    private $workCategoryRepository;

    /**
     * WorkCategoryFacade constructor.
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->workCategoryRepository = $entityManager->getRepository(WorkCategory::class);
    }

    /**
     * @param User $user
     * @return Query
     */
    public function queryWorkCategoriesByOwner(User $user): Query
    {
        return $this->workCategoryRepository
            ->findAllByOwner($user)
            ->getQuery();
    }
}
