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

namespace App\Domain\Work\Bus\Query\Work;

use App\Application\Interfaces\Bus\QueryHandlerInterface;
use App\Domain\Work\Facade\WorkFacade;

readonly class GetWorkQueryHandler implements QueryHandlerInterface
{
    public function __construct(private WorkFacade $workFacade) {}

    public function __invoke(GetWorkQuery $query): GetWorkQueryResult
    {
        $work = $this->workFacade->findById($query->id);

        return new GetWorkQueryResult($work);
    }
}
