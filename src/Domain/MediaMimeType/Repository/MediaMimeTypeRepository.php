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

namespace App\Domain\MediaMimeType\Repository;

use App\Domain\MediaMimeType\Entity\MediaMimeType;
use App\Domain\MediaType\Entity\MediaType;
use App\Domain\User\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

class MediaMimeTypeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MediaMimeType::class);
    }

    private function createMediaMimeTypeQueryBuilder(?string $indexBy = null): MediaMimeTypeQueryBuilder
    {
        return new MediaMimeTypeQueryBuilder($this->createQueryBuilder('media_mime_type', $indexBy));
    }

    public function allBy(
        iterable|User $user,
        iterable|MediaType|int|null $mediaType = null
    ): QueryBuilder {
        $builder = $this->createMediaMimeTypeQueryBuilder()
            ->leftJoinMedias();

        if (is_iterable($user)) {
            $builder = $builder->whereByMediaOwners($user);
        } else {
            $builder = $builder->whereByMediaOwner($user);
        }

        if ($mediaType !== null) {
            if (is_iterable($mediaType)) {
                $builder = $builder->whereByMediaTypes($mediaType);
            } else {
                $builder = $builder->whereByMediaType($mediaType);
            }
        }

        return $builder->getQueryBuilder();
    }

    public function byName(string $name): QueryBuilder
    {
        return $this->createMediaMimeTypeQueryBuilder()
            ->whereByName($name)
            ->getQueryBuilder();
    }

    public function formValidationMimeTypeName(): QueryBuilder
    {
        return $this->createMediaMimeTypeQueryBuilder('media_mime_type.name')
            ->selectName()
            ->whereByActive(true)
            ->getQueryBuilder();
    }
}
