<?php declare(strict_types=1);

/*
 *
 * This file is part of the FinalWorkSystem project.
 * (c) Vladimir Danilov
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace App\Model\MediaMimeType\Repository;

use App\Model\MediaMimeType\Entity\MediaMimeType;
use App\Model\MediaType\Entity\MediaType;
use App\Model\User\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\QueryBuilder;

class MediaMimeTypeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MediaMimeType::class);
    }

    private function baseQueryBuilder(): QueryBuilder
    {
        return $this->createQueryBuilder('media_mime_type')
            ->setCacheable(true);
    }

    public function allBy(
        iterable|User $user,
        iterable|MediaType|int $mediaType = null
    ): QueryBuilder {
        $queryBuilder = $this->baseQueryBuilder()
            ->leftJoin('media_mime_type.medias', 'medias');

        if (is_iterable($user)) {
            $queryBuilder->where('medias.owner IN(:user)')
                ->setParameter('user', $user);
        } else {
            $queryBuilder->where('medias.owner = :user')
                ->setParameter('user', $user);
        }

        if ($mediaType !== null) {
            if (is_iterable($mediaType)) {
                $queryBuilder->andWhere('medias.type IN(:mediaType)')
                    ->setParameter('mediaType', $mediaType);
            } else {
                $queryBuilder->andWhere('medias.type = :mediaType')
                    ->setParameter('mediaType', $mediaType);
            }
        }

        return $queryBuilder;
    }

    public function byName(string $name): QueryBuilder
    {
        return $this->baseQueryBuilder()
            ->where('media_mime_type.name = :name')
            ->setParameter('name', $name);
    }

    public function formValidationMimeTypeName(): QueryBuilder
    {
        return $this->createQueryBuilder('media_mime_type', 'media_mime_type.name')
            ->select('media_mime_type.name')
            ->where('media_mime_type.active = :active')
            ->setParameter('active', true);
    }
}
