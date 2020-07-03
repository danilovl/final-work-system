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

namespace App\Model\UserGroup;

use Doctrine\ORM\Query;
use App\Repository\UserGroupRepository;

class UserGroupFacade
{
    private UserGroupRepository $userGroupRepository;

    public function __construct(UserGroupRepository $userGroupRepository)
    {
        $this->userGroupRepository = $userGroupRepository;
    }

    public function queryAll(): Query
    {
        return $this->userGroupRepository
            ->all()
            ->getQuery();
    }
}
