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

namespace App\Domain\Event\Bus\Command\EditEvent;

use App\Application\Interfaces\Bus\CommandInterface;
use App\Domain\Event\Entity\Event;
use App\Domain\Event\Model\EventModel;
use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage('sync')]
readonly class EditEventCommand implements CommandInterface
{
    private function __construct(
        public EventModel $eventModel,
        public Event $event
    ) {}

    public static function create(EventModel $eventModel, Event $event): self
    {
        return new self($eventModel, $event);
    }
}
