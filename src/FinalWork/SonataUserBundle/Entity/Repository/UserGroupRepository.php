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

namespace FinalWork\SonataUserBundle\Entity\Repository;

use Doctrine\ORM\{
    QueryBuilder,
    EntityRepository
};

class UserGroupRepository extends EntityRepository
{
	/**
	 * @return QueryBuilder
	 */
	public function findAll(): QueryBuilder
    {
        return $this->createQueryBuilder('user_group');
    }
}
