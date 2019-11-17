<?php declare(strict_types=1);

/*
 *
 * This file is part of the FinalWorkSystem project.
 * (c) Vladimir Danilov
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace FinalWork\FinalWorkBundle\EventListener;

use FinalWork\FinalWorkBundle\Services\EntityManagerService;
use Doctrine\ORM\{
    ORMException,
    OptimisticLockException,
    ORMInvalidArgumentException
};
use Symfony\Component\Security\Core\Security;
use FinalWork\FinalWorkBundle\Constant\{
    WorkUserTypeConstant,
    SystemEventTypeConstant
};
use FinalWork\FinalWorkBundle\Entity\{
    Work,
    Task,
    Event,
    Media,
    Comment,
    SystemEvent,
    SystemEventType,
    ConversationMessage,
    SystemEventRecipient,
    ConversationParticipant
};
use FinalWork\SonataUserBundle\Entity\User;
use Symfony\Component\EventDispatcher\{
    GenericEvent,
    EventSubscriberInterface
};

class SystemEventSubscriber implements EventSubscriberInterface
{
    /**
     * @var EntityManagerService
     */
    private $em;

    /**
     * @var User
     */
    private $user;

    /**
     * SystemEventSubscriber constructor.
     *
     * @param EntityManagerService $entityManagerService
     * @param Security $security
     */
    public function __construct(
        EntityManagerService $entityManagerService,
        Security $security
    ) {
        $this->em = $entityManagerService;
        $this->user = $security->getUser();
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents(): array
    {
        return [
            Events::SYSTEM_WORK_CREATE => 'onWorkCreate',
            Events::SYSTEM_WORK_EDIT => 'onWorkEdit',
            Events::SYSTEM_WORK_AUTHOR_EDIT => 'onWorkAuthorEdit',
            Events::SYSTEM_TASK_CREATE => 'onTaskCreate',
            Events::SYSTEM_TASK_EDIT => 'onTaskEdit',
            Events::SYSTEM_TASK_COMPLETE => 'onTaskComplete',
            Events::SYSTEM_TASK_INCOMPLETE => 'onTaskInComplete',
            Events::SYSTEM_TASK_NOTIFY_COMPLETE => 'onTaskNotifyComplete',
            Events::SYSTEM_TASK_NOTIFY_INCOMPLETE => 'onTaskNotifyInComplete',
            Events::SYSTEM_VERSION_CREATE => 'onVersionCreate',
            Events::SYSTEM_VERSION_EDIT => 'onVersionEdit',
            Events::SYSTEM_DOCUMENT_CREATE => 'onDocumentCreate',
            Events::SYSTEM_EVENT_CREATE => 'onEventCreate',
            Events::SYSTEM_EVENT_EDIT => 'onEventEdit',
            Events::SYSTEM_EVENT_SWITCH_SKYPE => 'onEventSwitchSkype',
            Events::SYSTEM_EVENT_COMMENT_CREATE => 'onEventCommentCreate',
            Events::SYSTEM_EVENT_COMMENT_EDIT => 'onEventCommentEdit',
            Events::SYSTEM_EVENT_RESERVATION => 'onEventReservation',
            Events::SYSTEM_MESSAGE_CREATE => 'onMessageCreate',
            Events::SYSTEM_USER_EDIT => 'onUserEdit',
        ];
    }

    /**
     * @param GenericEvent $event
     *
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function onWorkCreate(GenericEvent $event): void
    {
        /** @var Work $work */
        $work = $event->getSubject();

        $systemEvent = new SystemEvent();
        $systemEvent->setWork($work);
        $systemEvent->setOwner($work->getSupervisor());
        $systemEvent->setType($this->em->getRepository(SystemEventType::class)
            ->find(SystemEventTypeConstant::WORK_CREATE)
        );

        $workUsers = $work->getAllUsers();

        /** @var User $user */
        foreach ($workUsers as $user) {
            if ($user->getId() !== $work->getSupervisor()->getId()) {
                $recipient = new SystemEventRecipient();
                $recipient->setRecipient($user);
                $systemEvent->addRecipient($recipient);
            }
        }

        $this->em->persist($systemEvent);
        $this->em->flush();
    }

    /**
     * @param GenericEvent $event
     *
     * @throws ORMInvalidArgumentException
     * @throws OptimisticLockException
     * @throws ORMException
     */
    public function onUserEdit(GenericEvent $event): void
    {
        /** @var User $user */
        $user = $event->getSubject();

        $systemEvent = new SystemEvent();
        $systemEvent->setOwner($this->user);
        $systemEvent->setType($this->em->getRepository(SystemEventType::class)
            ->find(SystemEventTypeConstant::USER_EDIT)
        );

        $recipientAuthor = new SystemEventRecipient();
        $recipientAuthor->setRecipient($user);
        $systemEvent->addRecipient($recipientAuthor);

        $this->em->persist($systemEvent);
        $this->em->flush();
    }

    /**
     * @param GenericEvent $event
     *
     * @throws ORMInvalidArgumentException
     * @throws OptimisticLockException
     * @throws ORMException
     */
    public function onWorkAuthorEdit(GenericEvent $event): void
    {
        /** @var Work $work */
        $work = $event->getSubject();

        $systemEvent = new SystemEvent();
        $systemEvent->setOwner($work->getSupervisor());
        $systemEvent->setType($this->em->getRepository(SystemEventType::class)
            ->find(SystemEventTypeConstant::USER_EDIT)
        );
        $systemEvent->setWork($work);

        $recipientAuthor = new SystemEventRecipient();
        $recipientAuthor->setRecipient($work->getAuthor());
        $systemEvent->addRecipient($recipientAuthor);

        $this->em->persist($systemEvent);
        $this->em->flush();
    }

    /**
     * @param GenericEvent $event
     *
     * @throws ORMInvalidArgumentException
     * @throws OptimisticLockException
     * @throws ORMException
     */
    public function onWorkEdit(GenericEvent $event): void
    {
        /** @var Work $work */
        $work = $event->getSubject();

        $systemEvent = new SystemEvent();
        $systemEvent->setWork($work);
        $systemEvent->setOwner($work->getSupervisor());
        $systemEvent->setType($this->em->getRepository(SystemEventType::class)
            ->find(SystemEventTypeConstant::WORK_EDIT)
        );

        $recipientAuthor = new SystemEventRecipient();
        $recipientAuthor->setRecipient($work->getAuthor());
        $systemEvent->addRecipient($recipientAuthor);

        if ($work->getOpponent()) {
            $recipientOpponent = new SystemEventRecipient();
            $recipientOpponent->setRecipient($work->getOpponent());
            $systemEvent->addRecipient($recipientOpponent);
        }

        if ($work->getConsultant()) {
            $recipientConsultant = new SystemEventRecipient();
            $recipientConsultant->setRecipient($work->getConsultant());
            $systemEvent->addRecipient($recipientConsultant);
        }

        $this->em->persist($systemEvent);
        $this->em->flush();
    }

    /**
     * @param GenericEvent $event
     *
     * @throws ORMInvalidArgumentException
     * @throws OptimisticLockException
     * @throws ORMException
     */
    public function onTaskCreate(GenericEvent $event): void
    {
        /** @var Task $task */
        $task = $event->getSubject();

        /** @var Work $work */
        $work = $task->getWork();

        $systemEvent = new SystemEvent();
        $systemEvent->setOwner($task->getOwner());
        $systemEvent->setWork($work);
        $systemEvent->setTask($task);
        $systemEvent->setType($this->em->getRepository(SystemEventType::class)
            ->find(SystemEventTypeConstant::TASK_CREATE)
        );

        $recipient = new SystemEventRecipient();
        $recipient->setRecipient($work->getAuthor());
        $systemEvent->addRecipient($recipient);

        $this->em->persist($systemEvent);
        $this->em->flush();
    }

    /**
     * @param GenericEvent $event
     *
     * @throws ORMInvalidArgumentException
     * @throws OptimisticLockException
     * @throws ORMException
     */
    public function onTaskEdit(GenericEvent $event): void
    {
        /** @var Task $task */
        $task = $event->getSubject();

        /** @var Work $work */
        $work = $task->getWork();

        $systemEvent = new SystemEvent();
        $systemEvent->setOwner($task->getOwner());
        $systemEvent->setWork($work);
        $systemEvent->setTask($task);
        $systemEvent->setType($this->em->getRepository(SystemEventType::class)
            ->find(SystemEventTypeConstant::TASK_EDIT)
        );

        $recipient = new SystemEventRecipient();
        $recipient->setRecipient($work->getAuthor());
        $systemEvent->addRecipient($recipient);

        $this->em->persist($systemEvent);
        $this->em->flush();
    }

    /**
     * @param GenericEvent $event
     *
     * @throws ORMInvalidArgumentException
     * @throws OptimisticLockException
     * @throws ORMException
     */
    public function onTaskComplete(GenericEvent $event): void
    {
        /** @var Task $task */
        $task = $event->getSubject();

        /** @var Work $work */
        $work = $task->getWork();

        $systemEvent = new SystemEvent();
        $systemEvent->setOwner($task->getOwner());
        $systemEvent->setWork($work);
        $systemEvent->setTask($task);
        $systemEvent->setType($this->em->getRepository(SystemEventType::class)
            ->find(SystemEventTypeConstant::TASK_COMPLETE)
        );

        $recipient = new SystemEventRecipient();
        $recipient->setRecipient($work->getAuthor());
        $systemEvent->addRecipient($recipient);

        $this->em->persist($systemEvent);
        $this->em->flush();
    }

    /**
     * @param GenericEvent $event
     *
     * @throws ORMInvalidArgumentException
     * @throws OptimisticLockException
     * @throws ORMException
     */
    public function onTaskInComplete(GenericEvent $event): void
    {
        /** @var Task $task */
        $task = $event->getSubject();

        /** @var Work $work */
        $work = $task->getWork();

        $systemEvent = new SystemEvent();
        $systemEvent->setOwner($task->getOwner());
        $systemEvent->setWork($work);
        $systemEvent->setTask($task);
        $systemEvent->setType($this->em->getRepository(SystemEventType::class)
            ->find(SystemEventTypeConstant::TASK_INCOMPLETE)
        );

        $recipient = new SystemEventRecipient();
        $recipient->setRecipient($work->getAuthor());
        $systemEvent->addRecipient($recipient);

        $this->em->persist($systemEvent);
        $this->em->flush();
    }

    /**
     * @param GenericEvent $event
     *
     * @throws ORMInvalidArgumentException
     * @throws OptimisticLockException
     * @throws ORMException
     */
    public function onTaskNotifyComplete(GenericEvent $event): void
    {
        /** @var Task $task */
        $task = $event->getSubject();

        /** @var Work $work */
        $work = $task->getWork();

        $systemEvent = new SystemEvent();
        $systemEvent->setOwner($work->getAuthor());
        $systemEvent->setWork($work);
        $systemEvent->setTask($task);
        $systemEvent->setType($this->em->getRepository(SystemEventType::class)
            ->find(SystemEventTypeConstant::TASK_NOTIFY_COMPLETE)
        );

        $recipient = new SystemEventRecipient();
        $recipient->setRecipient($task->getOwner());
        $systemEvent->addRecipient($recipient);

        $this->em->persist($systemEvent);
        $this->em->flush();
    }

    /**
     * @param GenericEvent $event
     *
     * @throws ORMInvalidArgumentException
     * @throws OptimisticLockException
     * @throws ORMException
     */
    public function onTaskNotifyInComplete(GenericEvent $event): void
    {
        /** @var Task $task */
        $task = $event->getSubject();

        /** @var Work $work */
        $work = $task->getWork();

        $systemEvent = new SystemEvent();
        $systemEvent->setOwner($task->getOwner());
        $systemEvent->setWork($work);
        $systemEvent->setTask($task);
        $systemEvent->setType($this->em->getRepository(SystemEventType::class)
            ->find(SystemEventTypeConstant::TASK_NOTIFY_INCOMPLETE)
        );

        $recipient = new SystemEventRecipient();
        $recipient->setRecipient($work->getAuthor());
        $systemEvent->addRecipient($recipient);

        $this->em->persist($systemEvent);
        $this->em->flush();
    }

    /**
     * @param GenericEvent $event
     *
     * @throws ORMInvalidArgumentException
     * @throws OptimisticLockException
     * @throws ORMException
     */
    public function onVersionCreate(GenericEvent $event): void
    {
        /** @var Media $media */
        $media = $event->getSubject();

        /** @var Work $work */
        $work = $media->getWork();

        $systemEvent = new SystemEvent();
        $systemEvent->setOwner($media->getOwner());
        $systemEvent->setWork($work);
        $systemEvent->setMedia($media);
        $systemEvent->setType($this->em->getRepository(SystemEventType::class)
            ->find(SystemEventTypeConstant::VERSION_CREATE)
        );

        $workUsers = $work->getUsers(true, true, true, true);
        /** @var User $user */
        foreach ($workUsers as $user) {
            if ($user->getId() !== $media->getOwner()->getId()) {
                $recipient = new SystemEventRecipient();
                $recipient->setRecipient($user);
                $systemEvent->addRecipient($recipient);
            }
        }

        $this->em->persist($systemEvent);
        $this->em->flush();
    }

    /**
     * @param GenericEvent $event
     *
     * @throws ORMInvalidArgumentException
     * @throws OptimisticLockException
     * @throws ORMException
     * @throws ORMException
     */
    public function onVersionEdit(GenericEvent $event): void
    {
        /** @var Media $media */
        $media = $event->getSubject();

        /** @var Work $work */
        $work = $media->getWork();

        $systemEvent = new SystemEvent();
        $systemEvent->setOwner($media->getOwner());
        $systemEvent->setWork($work);
        $systemEvent->setMedia($media);
        $systemEvent->setType($this->em->getRepository(SystemEventType::class)
            ->find(SystemEventTypeConstant::VERSION_EDIT)
        );

        $workUsers = $work->getUsers(true, true, true, true);
        /** @var User $user */
        foreach ($workUsers as $user) {
            if ($user->getId() !== $media->getOwner()->getId()) {
                $recipient = new SystemEventRecipient();
                $recipient->setRecipient($user);
                $systemEvent->addRecipient($recipient);
            }
        }

        $this->em->persist($systemEvent);
        $this->em->flush();
    }

    /**
     * @param GenericEvent $event
     *
     * @throws ORMInvalidArgumentException
     * @throws OptimisticLockException
     * @throws ORMException
     */
    public function onDocumentCreate(GenericEvent $event): void
    {
        /** @var Media $media */
        $media = $event->getSubject();

        /** @var User $owner */
        $owner = $media->getOwner();

        $systemEvent = new SystemEvent();
        $systemEvent->setOwner($owner);
        $systemEvent->setMedia($media);
        $systemEvent->setType($this->em->getRepository(SystemEventType::class)
            ->find(SystemEventTypeConstant::DOCUMENT_CREATE)
        );

        $recipientArray = $owner->getActiveAuthor(WorkUserTypeConstant::SUPERVISOR);

        /** @var User $user */
        foreach ($recipientArray as $user) {
            $recipientAuthor = new SystemEventRecipient();
            $recipientAuthor->setRecipient($user);
            $systemEvent->addRecipient($recipientAuthor);
        }

        $this->em->persist($systemEvent);
        $this->em->flush();
    }

    /**
     * @param GenericEvent $event
     *
     * @throws ORMInvalidArgumentException
     * @throws OptimisticLockException
     * @throws ORMException
     */
    public function onMessageCreate(GenericEvent $event): void
    {
        /** @var ConversationMessage $conversationMessage */
        $conversationMessage = $event->getSubject();
        $massageOwner = $conversationMessage->getOwner();
        $conversation = $conversationMessage->getConversation();

        $systemEvent = new SystemEvent();
        $systemEvent->setOwner($massageOwner);

        if ($conversation->getWork()) {
            $systemEvent->setWork($conversation->getWork());
        }

        $systemEvent->setConversation($conversation);
        $systemEvent->setType($this->em->getRepository(SystemEventType::class)
            ->find(SystemEventTypeConstant::MESSAGE_CREATE)
        );

        $participantArray = $conversation->getParticipants();

        /** @var ConversationParticipant $parcipant */
        foreach ($participantArray as $parcipant) {
            if ($massageOwner->getId() !== $parcipant->getUser()->getId()) {
                $recipientAuthor = new SystemEventRecipient();
                $recipientAuthor->setRecipient($parcipant->getUser());
                $systemEvent->addRecipient($recipientAuthor);
            }
        }

        $this->em->persist($systemEvent);
        $this->em->flush();
    }

    /**
     * @param GenericEvent $genericEvent
     *
     * @throws ORMInvalidArgumentException
     * @throws OptimisticLockException
     * @throws ORMException
     */
    public function onEventCreate(GenericEvent $genericEvent): void
    {
        /** @var Event $event */
        $event = $genericEvent->getSubject();

        $systemEvent = new SystemEvent();
        $systemEvent->setOwner($event->getOwner());
        $systemEvent->setEvent($event);
        $systemEvent->setType($this->em->getRepository(SystemEventType::class)
            ->find(SystemEventTypeConstant::EVENT_CREATE)
        );

        if ($event->getParticipant()) {
            if ($event->getParticipant()->getWork()) {
                $systemEvent->setWork($event->getParticipant()->getWork());
            }

            if ($event->getParticipant()->getUser()) {
                $recipient = new SystemEventRecipient();
                $recipient->setRecipient($event->getParticipant()->getUser());
                $systemEvent->addRecipient($recipient);
            }
        }

        $this->em->persist($systemEvent);
        $this->em->flush();
    }

    /**
     * @param GenericEvent $genericEvent
     *
     * @throws ORMInvalidArgumentException
     * @throws OptimisticLockException
     * @throws ORMException
     */
    public function onEventEdit(GenericEvent $genericEvent): void
    {
        /** @var Event $event */
        $event = $genericEvent->getSubject();

        $systemEvent = new SystemEvent();
        $systemEvent->setOwner($event->getOwner());
        $systemEvent->setEvent($event);
        $systemEvent->setType($this->em->getRepository(SystemEventType::class)
            ->find(SystemEventTypeConstant::EVENT_EDIT)
        );

        $recipient = new SystemEventRecipient();
        $recipient->setRecipient($event->getParticipant()->getUser());
        $systemEvent->addRecipient($recipient);

        $this->em->persist($systemEvent);
        $this->em->flush();
    }

    /**
     * @param GenericEvent $genericEvent
     * @throws ORMInvalidArgumentException
     * @throws OptimisticLockException
     * @throws ORMException
     */
    public function onEventSwitchSkype(GenericEvent $genericEvent): void
    {
        /** @var Event $event */
        $event = $genericEvent->getSubject();

        $systemEvent = new SystemEvent();
        $systemEvent->setOwner($event->getParticipant()->getUser());
        $systemEvent->setEvent($event);
        $systemEvent->setType($this->em->getRepository(SystemEventType::class)
            ->find(SystemEventTypeConstant::EVENT_SWITCH_SKYPE)
        );

        $recipient = new SystemEventRecipient();
        $recipient->setRecipient($event->getOwner());
        $systemEvent->addRecipient($recipient);

        $this->em->persist($systemEvent);
        $this->em->flush();
    }

    /**
     * @param GenericEvent $genericEvent
     *
     * @throws ORMInvalidArgumentException
     * @throws OptimisticLockException
     * @throws ORMException
     */
    public function onEventCommentCreate(GenericEvent $genericEvent): void
    {
        /** @var Comment $eventComment */
        $eventComment = $genericEvent->getSubject();

        /** @var Event $event */
        $event = $eventComment->getEvent();

        $systemEvent = new SystemEvent();
        $systemEvent->setOwner($eventComment->getOwner());
        $systemEvent->setEvent($event);

        if ($event->getParticipant()->getWork()) {
            $systemEvent->setWork($event->getParticipant()->getWork());
        }

        $systemEvent->setType($this->em->getRepository(SystemEventType::class)
            ->find(SystemEventTypeConstant::EVENT_COMMENT_CREATE)
        );

        $recipient = new SystemEventRecipient();

        $recipientUser = $event->getParticipant()->getUser();
        if ($eventComment->getOwner()->getId() !== $event->getOwner()->getId()) {
            $recipientUser = $event->getOwner();
        }

        $recipient->setRecipient($recipientUser);
        $systemEvent->addRecipient($recipient);

        $this->em->persist($systemEvent);
        $this->em->flush();
    }

    /**
     * @param GenericEvent $genericEvent
     *
     * @throws ORMInvalidArgumentException
     * @throws OptimisticLockException
     * @throws ORMException
     */
    public function onEventCommentEdit(GenericEvent $genericEvent): void
    {
        /** @var Comment $eventComment */
        $eventComment = $genericEvent->getSubject();

        /** @var Event $event */
        $event = $eventComment->getEvent();

        $systemEvent = new SystemEvent();
        $systemEvent->setOwner($eventComment->getOwner());
        $systemEvent->setEvent($event);

        if ($event->getParticipant()->getWork()) {
            $systemEvent->setWork($event->getParticipant()->getWork());
        }

        $systemEvent->setType($this->em->getRepository(SystemEventType::class)
            ->find(SystemEventTypeConstant::EVENT_COMMENT_EDIT)
        );

        $recipient = new SystemEventRecipient();

        $recipientUser = $event->getParticipant()->getUser();
        if ($eventComment->getOwner()->getId() !== $event->getOwner()->getId()) {
            $recipientUser = $event->getOwner();
        }

        $recipient->setRecipient($recipientUser);
        $systemEvent->addRecipient($recipient);

        $this->em->persist($systemEvent);
        $this->em->flush();
    }

    /**
     * @param GenericEvent $genericEvent
     * @throws ORMInvalidArgumentException
     * @throws OptimisticLockException
     * @throws ORMException
     */
    public function onEventReservation(GenericEvent $genericEvent): void
    {
        /** @var Event $event */
        $event = $genericEvent->getSubject();

        $systemEvent = new SystemEvent();
        $systemEvent->setOwner($event->getParticipant()->getUser());
        $systemEvent->setEvent($event);
        $systemEvent->setType($this->em->getRepository(SystemEventType::class)
            ->find(SystemEventTypeConstant::EVENT_CREATE)
        );

        $recipient = new SystemEventRecipient();
        $recipient->setRecipient($event->getOwner());
        $systemEvent->addRecipient($recipient);

        $this->em->persist($systemEvent);
        $this->em->flush();
    }
}
