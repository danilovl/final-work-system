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

use DateTime;
use FinalWork\FinalWorkBundle\Entity\{
    Event,
    EventParticipant
};
use Symfony\Component\Validator\Constraints as Assert;

class EventModel
{
    /**
     * @Assert\NotBlank()
     */
    public $type;

    /**
     * @var string|null
     */
    public $name;

    /**
     * @Assert\NotBlank()
     */
    public $address;

    /**
     * @var EventParticipant|null
     */
    public $participant;

    /**
     * @var DateTime|null
     * @Assert\Date()
     * @Assert\NotBlank()
     */
    public $start;

    /**
     * @var DateTime|null
     * @Assert\Date()
     * @Assert\NotBlank()
     */
    public $end;

    /**
     * @Assert\NotBlank()
     */
    public $owner;

    /**
     * @param Event $event
     * @return static
     */
    public static function fromEvent(Event $event): self
    {
        $model = new self();
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
