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

namespace FinalWork\FinalWorkBundle\Model\Media;

use Doctrine\ORM\{
    Query,
    EntityManager
};
use FinalWork\FinalWorkBundle\Entity\MediaCategory;
use FinalWork\FinalWorkBundle\Entity\Repository\MediaCategoryRepository;
use FinalWork\SonataUserBundle\Entity\User;

class MediaCategoryFacade
{
    /**
     * @var MediaCategoryRepository
     */
    private $mediaCategoryRepository;

    /**
     * MediaCategoryFacade constructor.
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->mediaCategoryRepository = $entityManager->getRepository(MediaCategory::class);
    }

    /**
     * @param User $user
     * @return Query
     */
    public function queryMediaCategoriesByOwner(User $user): Query
    {
        return $this->mediaCategoryRepository
            ->findAllByOwner($user)
            ->getQuery();
    }

    /**
     * @param User $user
     * @return array
     */
    public function getMediaCategoriesByOwner(User $user): array
    {
        return $this->mediaCategoryRepository
            ->findAllByOwner($user)
            ->getQuery()
            ->getResult();
    }

    /**
     * @param iterable $users
     * @return array
     */
    public function getMediaCategoriesByOwners(iterable $users): array
    {
        return $this->mediaCategoryRepository
            ->findAllByOwners($users)
            ->getQuery()
            ->getResult();
    }
}
