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

namespace FinalWork\FinalWorkBundle\Model\UserGroup;

use Doctrine\ORM\{
    Query,
    EntityManager
};
use FinalWork\SonataUserBundle\Entity\Group;
use FinalWork\SonataUserBundle\Entity\Repository\UserGroupRepository;

class UserGroupFacade
{
	/**
	 * @var UserGroupRepository
	 */
	private $userGroupRepository;

	/**
	 * UserGroupFacade constructor.
	 * @param EntityManager $entityManager
	 */
	public function __construct(EntityManager $entityManager)
	{
		$this->userGroupRepository = $entityManager->getRepository(Group::class);
	}

	/**
	 * @return Query
	 */
	public function queryAll(): Query
    {
       return $this->userGroupRepository
            ->findAll()
		    ->getQuery();
    }
}
