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

namespace FinalWork\FinalWorkBundle\Model\Event;

use FinalWork\FinalWorkBundle\Model\BaseModelFactory;
use Doctrine\ORM\{
    ORMException,
    OptimisticLockException
};
use FinalWork\FinalWorkBundle\Entity\Event;

class EventFactory extends BaseModelFactory
{
    /**
     * @param EventModel $eventModel
     * @param Event|null $event
     * @return Event
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function flushFromModel(
        EventModel $eventModel,
        Event $event = null
    ): Event {
        $event = $event ?? new Event;
        $event = $this->fromModel($event, $eventModel);

        $this->em->persist($event);
        $this->em->flush();

        return $event;
    }

    /**
     * @param Event $event
     * @param EventModel $eventModel
     * @return Event
     */
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
