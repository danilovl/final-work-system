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

namespace App\Form\EventSubscriber;

use DateTime;
use App\Entity\{
    User,
    Event
};
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\{
    FormEvent,
    FormEvents
};

class EventCommentSubscriber implements EventSubscriberInterface
{
    private User $user;
    private Event $event;

    public function __construct(User $user, Event $event)
    {
        $this->user = $user;
        $this->event = $event;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            FormEvents::PRE_SET_DATA => 'preSetData'
        ];
    }

    public function preSetData(FormEvent $formEvent): void
    {
        $form = $formEvent->getForm();
        if (!$this->event->isOwner($this->user) && $this->event->getStart() < new DateTime) {
            $form->remove('content');
        }
    }
}