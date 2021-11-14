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

namespace App\Model\User\Form\DataGrid;

use Doctrine\ORM\QueryBuilder;
use App\Repository\UserRepository;

class UserDataGrid
{
    public function __construct(private UserRepository $userRepository)
    {
    }

    public function queryBuilderAllByRole(string $userRole): QueryBuilder
    {
        return $this->userRepository->allByUserRole($userRole);
    }
}
