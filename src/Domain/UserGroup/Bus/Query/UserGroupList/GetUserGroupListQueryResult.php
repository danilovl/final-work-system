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

namespace App\Domain\UserGroup\Bus\Query\UserGroupList;

use App\Domain\UserGroup\Entity\Group;
use Knp\Component\Pager\Pagination\PaginationInterface;

readonly class GetUserGroupListQueryResult
{
    /**
     * @param PaginationInterface<int, Group> $groups
     */
    public function __construct(public PaginationInterface $groups) {}
}
