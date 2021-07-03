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

namespace App\Repository;

use App\DataTransferObject\Repository\MediaData;
use App\Entity\{
    User,
    Work,
    Media,
    MediaType
};
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\QueryBuilder;

class MediaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Media::class);
    }

    public function baseQueryBuilder(): QueryBuilder
    {
        return $this->createQueryBuilder('media')
            ->leftJoin('media.mimeType', 'mime_type')
            ->leftJoin('media.categories', 'categories')
            ->setCacheable(true);
    }

    public function mediaListByUserFilter(MediaData $mediaData): QueryBuilder
    {
        $queryBuilder = $this->createQueryBuilder('media')
            ->leftJoin('media.mimeType', 'mime_type')->addSelect('mime_type')
            ->leftJoin('media.categories', 'categories')->addSelect('categories')
            ->orderBy('media.createdAt', Criteria::DESC);

        if ($mediaData->users !== null) {
            $queryBuilder
                ->andWhere(
                    $queryBuilder->expr()->in('media.owner', ':users')
                )
                ->setParameter('users', $mediaData->users);
        }

        if ($mediaData->criteria !== null) {
            foreach ($mediaData->criteria as $field => $value) {
                if ($field === 'name' && !empty($value)) {
                    $queryBuilder->andWhere('media.name LIKE :m_name')
                        ->setParameter('m_name', '%' . $value . '%');
                }

                if ($field === 'categories' && !empty($value)) {
                    $queryBuilder
                        ->andWhere(
                            $queryBuilder->expr()->in('categories.id', ':c_category')
                        )
                        ->setParameter('c_category', $value);
                }

                if ($field === 'mimeType' && !empty($value)) {
                    $queryBuilder
                        ->andWhere(
                            $queryBuilder->expr()->in('mime_type.id', ':m_mimeType')
                        )
                        ->setParameter('m_mimeType', $value);
                }
            }
        }

        if ($mediaData->type !== null) {
            $queryBuilder->andWhere('media.type = :type')
                ->setParameter('type', $mediaData->type);
        }

        if ($mediaData->active) {
            $queryBuilder->andWhere('media.active = :active')
                ->setParameter('active', $mediaData->type);
        }

        return $queryBuilder;
    }

    public function allByWork(Work $work): QueryBuilder
    {
        return $this->createQueryBuilder('media')
            ->where('media.work = :work')
            ->orderBy('media.createdAt', Criteria::DESC)
            ->setParameter('work', $work);
    }

    public function allByUser(User $user, MediaType $type = null): QueryBuilder
    {
        $queryBuilder = $this->createQueryBuilder('media')
            ->where('media.owner = :user')
            ->orderBy('media.createdAt', Criteria::DESC)
            ->setParameter('user', $user);

        if ($type !== null) {
            $queryBuilder->leftJoin('media.type', 'type')->addSelect('type')
                ->andWhere('type = :type')
                ->setParameter('type', $type);
        }

        return $queryBuilder;
    }

    public function allByType(MediaType $mediaType): QueryBuilder
    {
        return $this->createQueryBuilder('media')
            ->where('media.type = :type')
            ->setParameter('type', $mediaType);
    }
}
