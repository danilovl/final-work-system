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

namespace FinalWork\FinalWorkBundle\Entity\Repository;

use Doctrine\ORM\{
    QueryBuilder,
    EntityRepository
};

class ApiUserRepository extends EntityRepository
{
    /**
     * @return QueryBuilder
     */
    public function baseQueryBuilder(): QueryBuilder
    {
        return $this->createQueryBuilder('api_user');
    }

    /**
     * @param string $apiKey
     * @return QueryBuilder
     */
    public function findByApiKey(string $apiKey): QueryBuilder
    {
        return $this->baseQueryBuilder()
            ->where('api_user.apiKey =:apiKey')
            ->setParameter('apiKey', $apiKey);
    }
}
