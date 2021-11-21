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

use App\Model\Event\Entity\Event;
use App\Model\EventAddress\Entity\EventAddress;
use App\Model\EventParticipant\Entity\EventParticipant;
use App\Model\EventType\Entity\EventType;
use DateTime;
use App\Model\User\Entity\User;

class EventModel
{
    public ?EventType $type = null;
    public ?string $name = null;
    public ?EventAddress $address = null;
    public ?EventParticipant $participant = null;
    public ?DateTime $start = null;
    public ?DateTime $end = null;
    public ?User $owner = null;

    public static function fromEvent(Event $event): self
    {
        $model = new self;
        $model->type = $event->getType();
        $model->name = $event->getName();
        $model->address = $event->getAddress();
        $model->participant = $event->getParticipant();
        $model->start = $event->getStart();
        $model->end = $event->getEnd();
        $model->owner = $event->getOwner();

        return $model;
    }
}
