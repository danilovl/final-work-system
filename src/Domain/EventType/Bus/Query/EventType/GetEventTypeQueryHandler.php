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

namespace App\Domain\EventType\Bus\Query\EventType;

use App\Application\Interfaces\Bus\QueryHandlerInterface;
use App\Domain\EventType\Facade\EventTypeFacade;

readonly class GetEventTypeQueryHandler implements QueryHandlerInterface
{
    public function __construct(private EventTypeFacade $eventTypeFacade) {}

    public function __invoke(GetEventTypeQuery $query): GetEventTypeQueryResult
    {
        $eventType = $this->eventTypeFacade->findById($query->id);

        return new GetEventTypeQueryResult($eventType);
    }
}
