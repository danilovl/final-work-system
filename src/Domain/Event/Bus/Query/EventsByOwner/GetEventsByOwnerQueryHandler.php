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

namespace App\Domain\Event\Bus\Query\EventsByOwner;

use App\Application\Interfaces\Bus\QueryHandlerInterface;
use App\Domain\Event\Facade\EventFacade;

readonly class GetEventsByOwnerQueryHandler implements QueryHandlerInterface
{
    public function __construct(private EventFacade $eventFacade) {}

    public function __invoke(GetEventsByOwnerQuery $query): GetEventsByOwnerQueryResult
    {
        $events = $this->eventFacade->listEventsByOwner($query->eventRepositoryDTO);

        return new GetEventsByOwnerQueryResult($events);
    }
}
