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

namespace App\DataGrid;

use Doctrine\ORM\{
    QueryBuilder,
    EntityManager
};
use App\Repository\UserRepository;
use App\Entity\User;

class UserDataGrid
{
    private UserRepository $userRepository;

    public function __construct(EntityManager $entityManager)
    {
        $this->userRepository = $entityManager->getRepository(User::class);
    }

    public function queryBuilderAllByRole(string $userRole): QueryBuilder
    {
        return $this->userRepository->allByUserRole($userRole);
    }
}
