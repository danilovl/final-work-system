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

namespace FinalWork\FinalWorkBundle\Entity\Repository;

use Doctrine\Common\Collections\{
    Criteria,
    Collection
};
use phpDocumentor\Reflection\Types\Null_;
use Doctrine\ORM\{
    QueryBuilder,
    EntityRepository
};
use FinalWork\SonataUserBundle\Entity\User;
use FinalWork\FinalWorkBundle\Entity\{
    Work,
    MediaType
};

class MediaRepository extends EntityRepository
{
    /**
     * @return QueryBuilder
     */
    public function baseQueryBuilder(): QueryBuilder
    {
        return $this->createQueryBuilder('media')
            ->leftJoin('media.mimeType', 'mime_type')
            ->leftJoin('media.categories', 'categories')
            ->setCacheable(true);
    }

    /**
     * @param Collection|User $users
     * @param MediaType $type
     * @param null $active
     * @param array|null $criteria
     * @return QueryBuilder
     */
    public function getMediaListByUserFilter(
        $users = null,
        MediaType $type = null,
        $active = null,
        array $criteria = null
    ): QueryBuilder {
        $queryBuilder = $this->createQueryBuilder('media')
            ->leftJoin('media.mimeType', 'mime_type')->addSelect('mime_type')
            ->leftJoin('media.categories', 'categories')->addSelect('categories')
            ->orderBy('media.createdAt', Criteria::DESC);

        if ($users) {
            $queryBuilder
                ->andWhere(
                    $queryBuilder->expr()->in('media.owner', ':user')
                )
                ->setParameter('user', $users);
        }

        if ($criteria !== null) {
            foreach ($criteria as $field => $value) {

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

        if ($type !== null) {
            $queryBuilder->andWhere('media.type = :type')
                ->setParameter('type', $type);
        }

        if ($active) {
            $queryBuilder->andWhere('media.active = :active')
                ->setParameter('active', $active);
        }

        return $queryBuilder;
    }

    /**
     * @param Work $work
     * @return QueryBuilder
     */
    public function findAllByWork(Work $work): QueryBuilder
    {
        return $this->createQueryBuilder('media')
            ->where('media.work = :work')
            ->orderBy('media.createdAt', Criteria::DESC)
            ->setParameter('work', $work);
    }

    /**
     * @param User $user
     * @param MediaType|null $type
     * @return QueryBuilder
     */
    public function findAllByUser(User $user, MediaType $type = null): QueryBuilder
    {
        $queryBuilder = $this->createQueryBuilder('media')
            ->where('media.owner = :user')
            ->orderBy('media.createdAt', Criteria::DESC)
            ->setParameter('user', $user);

        if ($type) {
            $queryBuilder->leftJoin('media.type', 'type')->addSelect('type')
                ->andWhere('type = :type')
                ->setParameter('type', $type);
        }

        return $queryBuilder;
    }

    /**
     * @param MediaType $mediaType
     * @return QueryBuilder
     */
    public function findAllByType(MediaType $mediaType): QueryBuilder
    {
        return $this->createQueryBuilder('media')
            ->where('media.type = :type')
            ->setParameter('type', $mediaType);
    }
}