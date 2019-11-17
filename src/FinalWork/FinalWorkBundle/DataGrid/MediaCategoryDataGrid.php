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
use FinalWork\FinalWorkBundle\Entity\MediaCategory;
use FinalWork\FinalWorkBundle\Entity\Repository\MediaCategoryRepository;
use FinalWork\SonataUserBundle\Entity\User;

class MediaCategoryDataGrid
{
    /**
     * @var MediaCategoryRepository
     */
    private $mediaCategoryRepository;

    /**
     * MediaCategoryDataGrid constructor.
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->mediaCategoryRepository = $entityManager->getRepository(MediaCategory::class);
    }

    /**
     * @param User $user
     * @return QueryBuilder
     */
    public function queryBuilderFindAllByOwner(User $user): QueryBuilder
    {
        return $this->mediaCategoryRepository->findAllByOwner($user);
    }
}
