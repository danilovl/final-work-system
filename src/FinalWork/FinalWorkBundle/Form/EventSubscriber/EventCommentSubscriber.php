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

namespace FinalWork\FinalWorkBundle\Form\EventSubscriber;

use DateTime;
use Exception;
use FinalWork\FinalWorkBundle\Entity\Event;
use FinalWork\SonataUserBundle\Entity\User;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\{
    FormEvent,
    FormEvents
};

class EventCommentSubscriber implements EventSubscriberInterface
{
    /**
     * @var User
     */
    private $user;

    /**
     * @var Event
     */
    private $event;

    /**
     * EventCommentSubscriber constructor.
     * @param User $user
     * @param Event $event
     */
    public function __construct(User $user, Event $event)
    {
        $this->user = $user;
        $this->event = $event;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents(): array
    {
        return [
            FormEvents::PRE_SET_DATA => 'preSetData'
        ];
    }

    /**
     * @param FormEvent $formEvent
     * @throws Exception
     */
    public function preSetData(FormEvent $formEvent): void
    {
        $form = $formEvent->getForm();
        if (!$this->event->isOwner($this->user) && $this->event->getStart() < new DateTime('now')) {
            $form->remove('content');
        }
    }
}