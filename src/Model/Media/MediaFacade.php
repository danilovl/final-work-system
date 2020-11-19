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

namespace App\Model\Media;

use Doctrine\ORM\Query;
use App\Entity\{
    Media,
    MediaType
};
use App\Repository\MediaRepository;
use App\Entity\User;

class MediaFacade
{
    private MediaRepository $mediaRepository;

    public function __construct(MediaRepository $mediaRepository)
    {
        $this->mediaRepository = $mediaRepository;
    }

    public function find(int $id): ?Media
    {
        return $this->mediaRepository->find($id);
    }

    public function findByMediaName(string $mediaName): ?Media
    {
        return $this->mediaRepository->findOneBy([
            'mediaName' => $mediaName
        ]);
    }

    /**
     * @return Media[]
     */
    public function findAll(int $offset, int $limit): array
    {
        return $this->mediaRepository
            ->baseQueryBuilder()
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function queryMediasByType(MediaType $mediaType): Query
    {
        return $this->mediaRepository
            ->allByType($mediaType)
            ->getQuery();
    }

    public function getMediaListQueryByUserFilter(
        $users,
        MediaType $type,
        $active = null,
        ?array $criteria = null
    ): Query {
        return $this->mediaRepository
            ->mediaListByUserFilter($users, $type, $active, $criteria)
            ->getQuery();
    }

    public function queryMediasQueryByUser(
        User $user,
        MediaType $type = null
    ): Query {
        return $this->mediaRepository
            ->allByUser($user, $type)
            ->getQuery();
    }
}
