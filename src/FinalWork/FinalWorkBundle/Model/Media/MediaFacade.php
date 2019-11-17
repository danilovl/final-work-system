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

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\{
    Query,
    EntityManager
};
use FinalWork\FinalWorkBundle\Entity\{
    Media,
    MediaType
};
use FinalWork\FinalWorkBundle\Entity\Repository\MediaRepository;
use FinalWork\SonataUserBundle\Entity\User;

class MediaFacade
{
    /**
     * @var MediaRepository
     */
    private $mediaRepository;

    /**
     * MediaFacade constructor.
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->mediaRepository = $entityManager->getRepository(Media::class);
    }

    /**
     * @param int $id
     * @return Media|null
     */
    public function find(int $id): ?Media
    {
        return $this->mediaRepository->find($id);
    }

    /**
     * @param MediaType $mediaType
     * @return Query
     */
    public function queryMediasByType(MediaType $mediaType): Query
    {
        return $this->mediaRepository
            ->findAllByType($mediaType)
            ->getQuery();
    }

    /**
     * @param Collection|User $users
     * @param MediaType $type
     * @param null $active
     * @param array|null $criteria
     * @return Query
     */
    public function getMediaListQueryByUserFilter(
        $users,
        MediaType $type,
        $active = null,
        ?array $criteria = null
    ): Query {
        return $this->mediaRepository
            ->getMediaListByUserFilter($users, $type, $active, $criteria)
            ->getQuery();
    }

    /**
     * @param User $user
     * @param MediaType|null $type
     * @return Query
     */
    public function queryMediasQueryByUser(
        User $user,
        MediaType $type = null
    ): Query {
        return $this->mediaRepository
            ->findAllByUser($user, $type)
            ->getQuery();
    }
}
