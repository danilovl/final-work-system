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

namespace App\Domain\Media\Facade;

use App\Domain\Media\DataTransferObject\MediaRepositoryData;
use App\Domain\Media\Entity\Media;
use App\Domain\Media\Repository\MediaRepository;
use App\Domain\MediaType\Entity\MediaType;
use App\Domain\User\Entity\User;
use Doctrine\ORM\Query;

readonly class MediaFacade
{
    public function __construct(private MediaRepository $mediaRepository) {}

    public function find(int $id): ?Media
    {
        /** @var Media|null $result */
        $result = $this->mediaRepository->find($id);

        return $result;
    }

    public function findByMediaName(string $mediaName): ?Media
    {
        /** @var Media|null $result */
        $result = $this->mediaRepository->findOneBy([
            'mediaName' => $mediaName
        ]);

        return $result;
    }

    /**
     * @return Media[]
     */
    public function findAll(int $offset, int $limit): array
    {
        /** @var Media[] $result */
        $result = $this->mediaRepository
            ->baseQueryBuilder()
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();

        return $result;
    }

    public function queryMediasByType(MediaType $mediaType): Query
    {
        return $this->mediaRepository
            ->allByType($mediaType)
            ->getQuery();
    }

    public function getMediaListQueryByUserFilter(MediaRepositoryData $mediaData): Query
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
