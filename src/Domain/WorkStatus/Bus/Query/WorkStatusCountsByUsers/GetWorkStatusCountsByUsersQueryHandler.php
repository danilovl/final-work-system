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

namespace App\Domain\WorkStatus\Bus\Query\WorkStatusCountsByUsers;

use App\Application\Interfaces\Bus\QueryHandlerInterface;
use App\Domain\WorkStatus\Facade\WorkStatusFacade;

readonly class GetWorkStatusCountsByUsersQueryHandler implements QueryHandlerInterface
{
    public function __construct(private WorkStatusFacade $workStatusFacade) {}

    public function __invoke(GetWorkStatusCountsByUsersQuery $query): GetWorkStatusCountsByUsersQueryResult
    {
        $counts = $this->workStatusFacade->listCountByUsers(
            userIds: $query->userIds,
            supervisor: $query->supervisor,
            type: $query->type,
            workStatus: $query->workStatus
        );

        return new GetWorkStatusCountsByUsersQueryResult($counts);
    }
}
