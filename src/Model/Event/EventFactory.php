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

namespace App\Model\Event;

use App\Model\BaseModelFactory;
use App\Entity\Event;

class EventFactory extends BaseModelFactory
{
    public function flushFromModel(
        EventModel $eventModel,
        Event $event = null
    ): Event {
        $event = $event ?? new Event;
        $event = $this->fromModel($event, $eventModel);

        $this->entityManagerService->persistAndFlush($event);

        return $event;
    }

    public function fromModel(
        Event $event,
        EventModel $eventModel
    ): Event {
        $event->setType($eventModel->type);
        $event->setName($eventModel->name);
        $event->setAddress($eventModel->address);
        $event->setParticipant($eventModel->participant);
        $event->setStart($eventModel->start);
        $event->setEnd($eventModel->end);
        $event->setOwner($eventModel->owner);

        return $event;
    }
}
