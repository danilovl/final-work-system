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

namespace App\Domain\Work\Bus\Query\WorksByUsersSupervisorStatus;

use App\Infrastructure\Interfaces\Bus\QueryHandlerInterface;
use App\Domain\Work\Facade\WorkFacade;

readonly class GetWorksByUsersSupervisorStatusQueryHandler implements QueryHandlerInterface
{
    public function __construct(private WorkFacade $workFacade) {}

    public function __invoke(GetWorksByUsersSupervisorStatusQuery $query): GetWorksByUsersSupervisorStatusQueryResult
    {
        $works = $this->workFacade->listByUsersSupervisorStatus(
            userIds: $query->userIds,
            supervisor: $query->supervisor,
            type: $query->type,
            workStatus: $query->workStatus
        );

        return new GetWorksByUsersSupervisorStatusQueryResult($works);
    }
}
