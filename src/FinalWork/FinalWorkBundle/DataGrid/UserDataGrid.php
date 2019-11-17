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

namespace FinalWork\FinalWorkBundle\DataGrid;

use Doctrine\ORM\{
    QueryBuilder,
    EntityManager
};
use FinalWork\SonataUserBundle\Entity\Repository\UserRepository;
use FinalWork\SonataUserBundle\Entity\User;

class UserDataGrid
{
    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * UserDataGrid constructor.
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->userRepository = $entityManager->getRepository(User::class);
    }

    /**
     * @param string $userRole
     * @return QueryBuilder
     */
    public function queryBuilderAllByRole(string $userRole): QueryBuilder
    {
        return $this->userRepository->findAllByUserRole($userRole);
    }
}
