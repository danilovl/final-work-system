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

namespace App\Domain\Work\Bus\Query\WorksByAuthorStatus;

use App\Infrastructure\Interfaces\Bus\QueryHandlerInterface;
use App\Domain\Work\Facade\WorkFacade;

readonly class GetWorksByAuthorStatusQueryHandler implements QueryHandlerInterface
{
    public function __construct(private WorkFacade $workFacade) {}

    public function __invoke(GetWorksByAuthorStatusQuery $query): GetWorksByAuthorStatusQueryResult
    {
        $works = $this->workFacade->listByAuthorStatus($query->workRepositoryDTO);

        return new GetWorksByAuthorStatusQueryResult($works);
    }
}
