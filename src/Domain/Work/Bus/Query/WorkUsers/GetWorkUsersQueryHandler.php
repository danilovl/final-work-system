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

use App\Infrastructure\Interfaces\Bus\QueryHandlerInterface;
use App\Domain\Work\Service\WorkService;

readonly class GetWorkUsersQueryHandler implements QueryHandlerInterface
{
    public function __construct(private WorkService $workService) {}

    public function __invoke(GetWorkUsersQuery $query): GetWorkUsersQueryResult
    {
        $users = $this->workService->getUsers(
            work: $query->work,
            author: $query->author,
            supervisor: $query->supervisor,
            consultant: $query->consultant,
            opponent: $query->opponent
        );

        return new GetWorkUsersQueryResult($users);
    }
}
