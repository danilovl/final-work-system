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

namespace App\Domain\Event\Bus\Query\EventsByParticipant;

use App\Infrastructure\Interfaces\Bus\QueryHandlerInterface;
use App\Domain\Event\Facade\EventFacade;

readonly class GetEventsByParticipantQueryHandler implements QueryHandlerInterface
{
    public function __construct(private EventFacade $eventFacade) {}

    public function __invoke(GetEventsByParticipantQuery $query): GetEventsByParticipantQueryResult
    {
        $events = $this->eventFacade->listByParticipant($query->eventRepositoryDTO);

        return new GetEventsByParticipantQueryResult($events);
    }
}
