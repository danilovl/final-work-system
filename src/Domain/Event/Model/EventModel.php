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

namespace App\Domain\Event\Model;

use App\Domain\Event\Entity\Event;
use App\Domain\EventAddress\Entity\EventAddress;
use App\Domain\EventParticipant\Entity\EventParticipant;
use App\Domain\EventType\Entity\EventType;
use App\Domain\User\Entity\User;
use DateTime;

class EventModel
{
    public ?int $id = null;

    public EventType $type;

    public ?string $name = null;

    public ?EventAddress $address = null;

    public ?EventParticipant $participant = null;

    public DateTime $start;

    public DateTime $end;

    public User $owner;

    public static function fromEvent(Event $event): self
    {
        $model = new self;
        $model->id = $event->getId();
        $model->type = $event->getType();
        $model->name = $event->getName();
        $model->address = $event->getAddress();
        $model->participant = $event->getParticipant();
        $model->start = $event->getStart();
        $model->end = $event->getEnd();
        $model->owner = $event->getOwner();

        return $model;
    }

    /**
     * @param EventParticipant[] $eventParticipantArray
     */
    public function setActualParticipant(array $eventParticipantArray): void
    {
        if ($this->participant === null) {
            return;
        }

        foreach ($eventParticipantArray as $eventParticipant) {
            if ($this->participant->getWork() === null || $this->participant->getUser() === null) {
                continue;
            }

            if ($eventParticipant->getWork() === null || $eventParticipant->getUser() === null) {
                continue;
            }

            if (
                $this->participant->getWork()->getId() === $eventParticipant->getWork()->getId() &&
                $this->participant->getUser()->getId() === $eventParticipant->getUser()->getId()
            ) {
                $this->participant = $eventParticipant;
            }
        }
    }
}
