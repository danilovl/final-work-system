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

namespace App\Domain\EventCalendar\Bus\Query\UserEventsCalendar;

use App\Application\Interfaces\Bus\QueryInterface;
use App\Domain\Event\Entity\Event;
use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage('sync')]
readonly class GetUserEventsCalendarQuery implements QueryInterface
{
    private function __construct(
        public Event $event,
        public array $userEvents
    ) {}

    public static function create(
        Event $event,
        array $userEvents
    ): self {
        return new self($event, $userEvents);
    }
}
