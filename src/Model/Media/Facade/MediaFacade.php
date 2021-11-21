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

namespace App\Model\Media\Facade;

use App\DataTransferObject\Repository\MediaData;
use App\Model\Media\Entity\Media;
use App\Model\Media\Repository\MediaRepository;
use App\Model\MediaType\Entity\MediaType;
use App\Model\User\Entity\User;
use Doctrine\ORM\Query;

class MediaFacade
{
    public function __construct(private MediaRepository $mediaRepository)
    {
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

    public function getMediaListQueryByUserFilter(MediaData $mediaData): Query
    {
        return $this->mediaRepository
            ->mediaListByUserFilter($mediaData)
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
