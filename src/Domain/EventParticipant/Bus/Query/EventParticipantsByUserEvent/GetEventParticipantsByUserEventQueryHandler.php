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

namespace App\Domain\EventParticipant\Bus\Query\EventParticipantsByUserEvent;

use App\Application\Interfaces\Bus\QueryHandlerInterface;
use App\Domain\EventParticipant\Facade\EventParticipantFacade;

readonly class GetEventParticipantsByUserEventQueryHandler implements QueryHandlerInterface
{
    public function __construct(private EventParticipantFacade $eventParticipantFacade) {}

    public function __invoke(GetEventParticipantsByUserEventQuery $query): GetEventParticipantsByUserEventQueryResult
    {
        $eventParticipants = $this->eventParticipantFacade->listByUserEvent(
            user: $query->user,
            event: $query->event
        );

        return new GetEventParticipantsByUserEventQueryResult($eventParticipants);
    }
}
