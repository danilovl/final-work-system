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

use App\Domain\MediaType\Entity\MediaType;
use App\Domain\User\Entity\User;
use App\Infrastructure\Persistence\Doctrine\Repository\BaseQueryBuilder;

class MediaMimeTypeQueryBuilder extends BaseQueryBuilder
{
    public function leftJoinMedias(): self
    {
        $this->queryBuilder->leftJoin('media_mime_type.medias', 'medias');

        return $this;
    }

    public function byMediaOwner(User $user): self
    {
        $this->queryBuilder
            ->andWhere('medias.owner = :user')
            ->setParameter('user', $user);

        return $this;
    }

    public function byMediaOwners(iterable $users): self
    {
        $this->queryBuilder
            ->andWhere('medias.owner IN(:user)')
            ->setParameter('user', $users);

        return $this;
    }

    public function byMediaType(MediaType|int $mediaType): self
    {
        $this->queryBuilder
            ->andWhere('medias.type = :mediaType')
            ->setParameter('mediaType', $mediaType);

        return $this;
    }

    public function byMediaTypes(iterable $mediaTypes): self
    {
        $this->queryBuilder
            ->andWhere('medias.type IN(:mediaType)')
            ->setParameter('mediaType', $mediaTypes);

        return $this;
    }

    public function byName(string $name): self
    {
        $this->queryBuilder
            ->andWhere('media_mime_type.name = :name')
            ->setParameter('name', $name);

        return $this;
    }

    public function selectName(): self
    {
        $this->queryBuilder->select('media_mime_type.name');

        return $this;
    }

    public function byActive(bool $active): self
    {
        $this->queryBuilder
            ->andWhere('media_mime_type.active = :active')
            ->setParameter('active', $active);

        return $this;
    }
}
