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

namespace App\Domain\UserGroup\Facade;

use App\Domain\UserGroup\Repository\UserGroupRepository;
use Doctrine\ORM\Query;

readonly class UserGroupFacade
{
    public function __construct(private UserGroupRepository $userGroupRepository) {}

    public function queryAll(): Query
    {
        return $this->userGroupRepository
            ->all()
            ->getQuery();
    }
}
