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

use Doctrine\ORM\{
    QueryBuilder,
    EntityRepository
};

class MediaMimeTypeRepository extends EntityRepository
{
    /**
     * @return QueryBuilder
     */
    private function baseQueryBuilder(): QueryBuilder
    {
        return $this->createQueryBuilder('media_mime_type')
            ->setCacheable(true);
    }

    /**
     * @param $user
     * @param bool|array $mediaType
     * @return QueryBuilder
     */
    public function findAllBy($user, $mediaType = false): QueryBuilder
    {
        $queryBuilder = $this->baseQueryBuilder()
            ->leftJoin('media_mime_type.medias', 'medias');

        if (is_iterable($user)) {
            $queryBuilder->where('medias.owner IN(:user)')
                ->setParameter('user', $user);
        } else {
            $queryBuilder->where('medias.owner = :user')
                ->setParameter('user', $user);
        }

        if ($mediaType) {
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

    /**
     * @param string $name
     * @return QueryBuilder
     */
    public function findByName(string $name): QueryBuilder
    {
        return $this->baseQueryBuilder()
            ->where('media_mime_type.name = :name')
            ->setParameter('name', $name);
    }

    /**
     * @return QueryBuilder
     */
    public function getFormValidationMimeTypeName(): QueryBuilder
    {
        return $this->createQueryBuilder('media_mime_type', 'media_mime_type.name')
            ->select('media_mime_type.name')
            ->where('media_mime_type.active = :active')
            ->setParameter('active', true);
    }
}
