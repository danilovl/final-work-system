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

namespace App\Domain\WorkStatus\Bus\Query\WorkStatusList;

use App\Infrastructure\Interfaces\Bus\QueryHandlerInterface;
use App\Domain\WorkStatus\Facade\WorkStatusFacade;

readonly class GetWorkStatusListQueryHandler implements QueryHandlerInterface
{
    public function __construct(private WorkStatusFacade $workStatusFacade) {}

    public function __invoke(GetWorkStatusListQuery $query): GetWorkStatusListQueryResult
    {
        $workStatuses = $this->workStatusFacade->list($query->limit);

        return new GetWorkStatusListQueryResult($workStatuses);
    }
}
