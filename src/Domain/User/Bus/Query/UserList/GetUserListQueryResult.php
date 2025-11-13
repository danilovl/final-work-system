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

namespace App\Domain\User\Bus\Query\UserList;

use App\Domain\User\Entity\User;
use Knp\Component\Pager\Pagination\PaginationInterface;

readonly class GetUserListQueryResult
{
    /**
     * @param PaginationInterface<int, User> $users
     */
    public function __construct(public PaginationInterface $users) {}
}
