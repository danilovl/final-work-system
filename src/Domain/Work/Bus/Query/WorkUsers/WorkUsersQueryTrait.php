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

namespace App\Domain\Work\Bus\Query\WorkUsers;

use App\Domain\User\Entity\User;
use App\Domain\Work\Entity\Work;

trait WorkUsersQueryTrait
{
    /**
     * @return User[]
     */
    private function getWorkUsers(
        Work $work,
        bool $author = false,
        bool $supervisor = false,
        bool $consultant = false,
        bool $opponent = false
    ): array {
        $getWorkUsersQuery = GetWorkUsersQuery::create(
            work: $work,
            author: $author,
            supervisor: $supervisor,
            consultant: $consultant,
            opponent: $opponent
        );

        /** @var GetWorkUsersQueryResult $getWorkUsersQueryResult */
        $getWorkUsersQueryResult = $this->queryBus->handle($getWorkUsersQuery);

        return $getWorkUsersQueryResult->users;
    }

    /**
     * @return User[]
     */
    private function getAllWorkUsers(Work $work): array
    {
        return $this->getWorkUsers(
            work: $work,
            author: true,
            supervisor: true,
            consultant: true,
            opponent: true
        );
    }
}
