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

namespace App\Domain\Event\Bus\Query\EventQueryByWork;

use App\Application\Interfaces\Bus\QueryHandlerInterface;
use App\Domain\Event\Facade\EventFacade;

readonly class GetEventQueryByWorkQueryHandler implements QueryHandlerInterface
{
    public function __construct(private EventFacade $eventFacade) {}

    public function __invoke(GetEventQueryByWorkQuery $query): GetEventQueryByWorkQueryResult
    {
        $result = $this->eventFacade->queryByWork($query->work);

        return new GetEventQueryByWorkQueryResult($result);
    }
}
