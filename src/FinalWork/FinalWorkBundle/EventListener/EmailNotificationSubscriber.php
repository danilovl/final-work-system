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

use FinalWork\FinalWorkBundle\Constant\WorkUserTypeConstant;
use Symfony\Component\Translation\TranslatorInterface;
use FinalWork\FinalWorkBundle\Entity\{
    Work,
    Task,
    Media,
    Event,
    Comment,
    ConversationMessage,
    ConversationParticipant
};
use FinalWork\SonataUserBundle\Entity\User;
use RuntimeException;
use Swift_Mailer;
use Swift_Message;
use Symfony\Bridge\Twig\TwigEngine;
use Symfony\Component\EventDispatcher\{
    GenericEvent,
    EventSubscriberInterface
};
use Twig\Error\Error;

class EmailNotificationSubscriber implements EventSubscriberInterface
{
    /**
     * @var bool
     */
    private static $isEnable = true;

    /**
     * @var Swift_Mailer
     */
    private $mailer;

    /**
     * @var TwigEngine
     */
    private $twig;

    /**
     * @var string
     */
    private $sender;

    /**
     * @var string
     */
    private $locale;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var string
     */
    private $translatorDomain;

    /**
     * SystemNotificationSubscriber constructor
     *
     * @param Swift_Mailer $mailer
     * @param TwigEngine $twig
     * @param TranslatorInterface $translator
     * @param array $parameters
     */
    public function __construct(
        Swift_Mailer $mailer,
        TwigEngine $twig,
        TranslatorInterface $translator,
        array $parameters
    ) {
        $this->mailer = $mailer;
        $this->twig = $twig;
        $this->translator = clone $translator;
        $this->sender = $parameters['sender'];
        $this->locale = $parameters['default_locale'];
        $this->translatorDomain = $parameters['translator_domain'];
        self::$isEnable = $parameters['enable'];

        $this->initTranslatorLocale($this->locale);
    }

    /**
     * @param string $locale
     */
    private function initTranslatorLocale(string $locale): void
    {
        $this->translator->setLocale($locale);
    }

    /**
     * @param string $name
     * @return string
     */
    private function getTemplate(string $name): string
    {
        return "@FinalWork/email_notification/{$this->locale}/{$name}.html.twig";
    }

    /**
     * @param string $trans
     * @return string
     */
    private function trans(string $trans): string
    {
        return $this->translator->trans("finalwork.email_notification.{$trans}", [], $this->translatorDomain);
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents(): array
    {
        if (!self::$isEnable) {
            return [];
        }

        return [
            Events::NOTIFICATION_WORK_CREATE => 'onWorkCreate',
            Events::NOTIFICATION_WORK_EDIT => 'onWorkEdit',
            Events::NOTIFICATION_TASK_CREATE => 'onTaskCreate',
            Events::NOTIFICATION_TASK_EDIT => 'onTaskEdit',
            Events::NOTIFICATION_TASK_COMPLETE => 'onTaskComplete',
            Events::NOTIFICATION_TASK_INCOMPLETE => 'onTaskInComplete',
            Events::NOTIFICATION_TASK_NOTIFY_COMPLETE => 'onTaskNotifyComplete',
            Events::NOTIFICATION_TASK_NOTIFY_INCOMPLETE => 'onTaskNotifyInComplete',
            Events::NOTIFICATION_VERSION_CREATE => 'onVersionCreate',
            Events::NOTIFICATION_VERSION_EDIT => 'onVersionEdit',
            Events::NOTIFICATION_DOCUMENT_CREATE => 'onDocumentCreate',
            Events::NOTIFICATION_EVENT_CREATE => 'onEventCreate',
            Events::NOTIFICATION_EVENT_EDIT => 'onEventEdit',
            Events::NOTIFICATION_EVENT_SWITCH_SKYPE => 'onEventSwitchSkype',
            Events::NOTIFICATION_EVENT_COMMENT_CREATE => 'onEventCommentCreate',
            Events::NOTIFICATION_EVENT_COMMENT_EDIT => 'onEventCommentEdit',
            Events::NOTIFICATION_EVENT_RESERVATION => 'onEventReservation',
            Events::NOTIFICATION_MESSAGE_CREATE => 'onMessageCreate',
            Events::NOTIFICATION_USER_CREATE => 'onUserCreate',
            Events::NOTIFICATION_USER_EDIT => 'onUserEdit',
        ];
    }

    /**
     * @param GenericEvent $event
     *
     * @throws RuntimeException
     * @throws Error
     */
    public function onUserCreate(GenericEvent $event): void
    {
        /** @var User $user */
        $user = $event->getSubject();

        $subject = $this->trans('subject.user_create');
        $to = $user->getEmail();
        $body = $this->twig->render($this->getTemplate('user_create'), [
            'username' => $user->getUsername(),
            'password' => $user->getPassword()
        ]);

        $this->sendEmail($subject, $to, $this->sender, $body);
    }

    /**
     * @param GenericEvent $event
     *
     * @throws RuntimeException
     * @throws Error
     */
    public function onWorkCreate(GenericEvent $event): void
    {
        /** @var Work $work */
        $work = $event->getSubject();

        $subject = $this->trans('subject.work_create');
        $author = $work->getAuthor();
        $opponent = $work->getOpponent();
        $consultant = $work->getConsultant();

        $to = null;
        $bodyParams = [];
        if ($author !== null) {
            $to = $author->getEmail();
            $bodyParams = [
                'work' => $work,
                'role' => 'autora'
            ];
        }

        if ($opponent !== null) {
            $to = $opponent->getEmail();
            $bodyParams = [
                'work' => $work,
                'role' => 'opponenta'
            ];
        }

        if ($consultant !== null) {
            $to = $consultant->getEmail();
            $bodyParams = [
                'work' => $work,
                'role' => 'konzultanta'
            ];
        }

        if ($to !== null) {
            $body = $this->twig->render($this->getTemplate('work_create'), $bodyParams);
            $this->sendEmail($subject, $to, $this->sender, $body);
        }
    }

    /**
     * @param GenericEvent $event
     *
     * @throws RuntimeException
     * @throws Error
     */
    public function onUserEdit(GenericEvent $event): void
    {
        /** @var User $user */
        $user = $event->getSubject();

        $subject = $this->trans('subject.user_edit');
        $to = $user->getEmail();
        $body = $this->twig->render($this->getTemplate('user_edit'));

        $this->sendEmail($subject, $to, $this->sender, $body);
    }

    /**
     * @param GenericEvent $event
     *
     * @throws RuntimeException
     * @throws Error
     */
    public function onWorkEdit(GenericEvent $event): void
    {
        /** @var Work $work */
        $work = $event->getSubject();

        $subject = $this->trans('subject.work_edit');
        $to = $work->getAuthor()->getEmail();
        $body = $this->twig->render($this->getTemplate('work_edit'), [
            'work' => $work
        ]);

        $this->sendEmail($subject, $to, $this->sender, $body);
    }

    /**
     * @param GenericEvent $event
     *
     * @throws RuntimeException
     * @throws Error
     */
    public function onTaskCreate(GenericEvent $event): void
    {
        /** @var Task $task */
        $task = $event->getSubject();
        /** @var Work $work */
        $work = $task->getWork();

        $subject = $this->trans('subject.task_create');
        $to = $work->getAuthor()->getEmail();
        $body = $this->twig->render($this->getTemplate('task_create'), [
            'work' => $work,
            'task' => $task
        ]);

        $this->sendEmail($subject, $to, $this->sender, $body);
    }

    /**
     * @param GenericEvent $event
     *
     * @throws RuntimeException
     * @throws Error
     */
    public function onTaskEdit(GenericEvent $event): void
    {
        /** @var Task $task */
        $task = $event->getSubject();
        /** @var Work $work */
        $work = $task->getWork();

        $subject = $this->trans('subject.task_edit');
        $to = $work->getAuthor()->getEmail();
        $body = $this->twig->render($this->getTemplate('task_edit'), [
            'work' => $work,
            'task' => $task
        ]);

        $this->sendEmail($subject, $to, $this->sender, $body);
    }

    /**
     * @param GenericEvent $event
     *
     * @throws RuntimeException
     * @throws Error
     */
    public function onTaskComplete(GenericEvent $event): void
    {
        /** @var Task $task */
        $task = $event->getSubject();
        /** @var Work $work */
        $work = $task->getWork();

        $subject = $this->trans('subject.task_complete');
        $to = $work->getAuthor()->getEmail();
        $body = $this->twig->render($this->getTemplate('task_complete'), [
            'work' => $work,
            'task' => $task
        ]);

        $this->sendEmail($subject, $to, $this->sender, $body);
    }

    /**
     * @param GenericEvent $event
     *
     * @throws RuntimeException
     * @throws Error
     */
    public function onTaskInComplete(GenericEvent $event): void
    {
        /** @var Task $task */
        $task = $event->getSubject();
        /** @var Work $work */
        $work = $task->getWork();

        $subject = $this->trans('subject.task_in_complete');
        $to = $work->getSupervisor()->getEmail();
        $body = $this->twig->render($this->getTemplate('task_incomplete'), [
            'work' => $work,
            'task' => $task
        ]);

        $this->sendEmail($subject, $to, $this->sender, $body);
    }

    /**
     * @param GenericEvent $event
     *
     * @throws RuntimeException
     * @throws Error
     */
    public function onTaskNotifyComplete(GenericEvent $event): void
    {
        /** @var Task $task */
        $task = $event->getSubject();
        /** @var Work $work */
        $work = $task->getWork();

        $subject = $this->trans('subject.task_notify_complete');
        $to = $work->getSupervisor()->getEmail();
        $body = $this->twig->render($this->getTemplate('task_notify_complete'), [
            'work' => $work,
            'task' => $task
        ]);

        $this->sendEmail($subject, $to, $this->sender, $body);
    }

    /**
     * @param GenericEvent $event
     *
     * @throws RuntimeException
     * @throws Error
     */
    public function onTaskNotifyInComplete(GenericEvent $event): void
    {
        /** @var Task $task */
        $task = $event->getSubject();
        /** @var Work $work */
        $work = $task->getWork();

        $subject = $this->trans('subject.task_notify_in_complete');
        $to = $work->getSupervisor()->getEmail();
        $body = $this->twig->render($this->getTemplate('task_notify_incomplete'), [
            'work' => $work,
            'task' => $task
        ]);

        $this->sendEmail($subject, $to, $this->sender, $body);
    }

    /**
     * @param GenericEvent $event
     *
     * @throws RuntimeException
     * @throws Error
     */
    public function onVersionCreate(GenericEvent $event): void
    {
        /** @var Media $media */
        $media = $event->getSubject();
        /** @var Work $work */
        $work = $media->getWork();

        $subject = $this->trans('subject.version_create');
        $body = $this->twig->render($this->getTemplate('work_version_create'), [
            'media' => $media,
            'work' => $work
        ]);

        $workUsers = $work->getAllUsers();

        /** @var User $user */
        foreach ($workUsers as $user) {
            if ($user->getId() !== $media->getOwner()->getId()) {
                $to = $user->getEmail();
                $this->sendEmail($subject, $to, $this->sender, $body);
            }
        }
    }

    /**
     * @param GenericEvent $event
     *
     * @throws RuntimeException
     * @throws Error
     */
    public function onVersionEdit(GenericEvent $event): void
    {
        /** @var Media $media */
        $media = $event->getSubject();
        /** @var Work $work */
        $work = $media->getWork();

        $subject = $this->trans('subject.version_edit');
        $body = $this->twig->render($this->getTemplate('work_version_edit'), [
            'media' => $media,
            'work' => $work
        ]);

        $workUsers = $work->getAllUsers();

        /** @var User $user */
        foreach ($workUsers as $user) {
            if ($user->getId() !== $media->getOwner()->getId()) {
                $to = $user->getEmail();
                $this->sendEmail($subject, $to, $this->sender, $body);
            }
        }
    }

    /**
     * @param GenericEvent $event
     *
     * @throws RuntimeException
     * @throws Error
     */
    public function onDocumentCreate(GenericEvent $event): void
    {
        /** @var Media $media */
        $media = $event->getSubject();
        /** @var User $owner */
        $owner = $media->getOwner();

        $subject = $this->trans('subject.document_create');
        $body = $this->twig->render($this->getTemplate('document_create'), [
            'media' => $media
        ]);
        $recipientArray = $owner->getActiveAuthor(WorkUserTypeConstant::SUPERVISOR);

        /** @var User $user */
        foreach ($recipientArray as $user) {
            $this->sendEmail($subject, $user->getEmail(), $this->sender, $body);
        }
    }

    /**
     * @param GenericEvent $event
     *
     * @throws RuntimeException
     * @throws Error
     */
    public function onMessageCreate(GenericEvent $event): void
    {
        /** @var ConversationMessage $conversationMessage */
        $conversationMessage = $event->getSubject();
        $conversation = $conversationMessage->getConversation();

        $subject = $this->trans('subject.message_create');
        /** @var ConversationParticipant $parcipant */
        foreach ($conversation->getParticipants() as $parcipant) {
            $to = $parcipant->getUser()->getEmail();

            if ($conversationMessage->getOwner()->getId() !== $parcipant->getUser()->getId()) {
                $body = $this->twig->render($this->getTemplate('message_create'), [
                    'sender' => $conversationMessage->getOwner(),
                    'conversation' => $conversation
                ]);
                $this->sendEmail($subject, $to, $this->sender, $body);
            }
        }
    }

    /**
     * @param GenericEvent $genericEvent
     *
     * @throws RuntimeException
     * @throws Error
     */
    public function onEventCreate(GenericEvent $genericEvent): void
    {
        /** @var Event $event */
        $event = $genericEvent->getSubject();

        $subject = $this->trans('subject.event_create');
        $to = $event->getParticipant()->getUser()->getEmail();
        $body = $this->twig->render($this->getTemplate('event_create'), [
            'user' => $event->getOwner(),
            'event' => $event
        ]);

        $this->sendEmail($subject, $to, $this->sender, $body);
    }

    /**
     * @param GenericEvent $genericEvent
     *
     * @throws RuntimeException
     * @throws Error
     */
    public function onEventEdit(GenericEvent $genericEvent): void
    {
        /** @var Event $event */
        $event = $genericEvent->getSubject();

        $subject = $this->trans('subject.event_edit');
        $to = $event->getParticipant()->getUser()->getEmail();
        $body = $this->twig->render($this->getTemplate('event_edit'), [
            'event' => $event
        ]);

        $this->sendEmail($subject, $to, $this->sender, $body);
    }

    /**
     * @param GenericEvent $genericEvent
     *
     * @throws RuntimeException
     * @throws Error
     */
    public function onEventSwitchSkype(GenericEvent $genericEvent): void
    {
        /** @var Event $event */
        $event = $genericEvent->getSubject();

        $subject = $this->trans('subject.event_switch_skype');
        $to = $event->getOwner()->getEmail();
        $body = $this->twig->render($this->getTemplate('event_switch_skype'), [
            'event' => $event
        ]);

        $this->sendEmail($subject, $to, $this->sender, $body);
    }

    /**
     * @param GenericEvent $genericEvent
     *
     * @throws RuntimeException
     * @throws Error
     */
    public function onEventCommentCreate(GenericEvent $genericEvent): void
    {
        /** @var Comment $eventComment */
        $eventComment = $genericEvent->getSubject();
        /** @var Event $event */
        $event = $eventComment->getEvent();

        $to = $event->getParticipant()->getUser()->getEmail();
        $user = $event->getParticipant()->getUser();

        if ($eventComment->getOwner()->getId() !== $event->getOwner()->getId()) {
            $to = $eventComment->getOwner()->getEmail();
            $user = $eventComment->getOwner();
        }

        $subject = $this->trans('subject.event_comment_create');
        $body = $this->twig->render($this->getTemplate('event_comment_create'), [
            'user' => $user,
            'event' => $event
        ]);

        $this->sendEmail($subject, $to, $this->sender, $body);
    }

    /**
     * @param GenericEvent $genericEvent
     *
     * @throws RuntimeException
     * @throws Error
     */
    public function onEventCommentEdit(GenericEvent $genericEvent): void
    {
        /** @var Comment $eventComment */
        $eventComment = $genericEvent->getSubject();
        /** @var Event $event */
        $event = $eventComment->getEvent();

        $to = $event->getParticipant()->getUser()->getEmail();
        $user = $event->getParticipant()->getUser();

        if ($eventComment->getOwner()->getId() !== $event->getOwner()->getId()) {
            $to = $eventComment->getOwner()->getEmail();
            $user = $eventComment->getOwner();
        }

        $subject = $this->trans('subject.event_comment_edit');
        $body = $this->twig->render($this->getTemplate('event_comment_edit'), [
            'user' => $user,
            'event' => $event
        ]);

        $this->sendEmail($subject, $to, $this->sender, $body);
    }

    /**
     * @param GenericEvent $genericEvent
     * @throws RuntimeException
     * @throws Error
     */
    public function onEventReservation(GenericEvent $genericEvent): void
    {
        /** @var Event $event */
        $event = $genericEvent->getSubject();

        $subject = $this->trans('subject.event_reservation');
        $to = $event->getOwner()->getEmail();
        $body = $this->twig->render($this->getTemplate('event_create'), [
            'user' => $event->getParticipant()->getUser(),
            'event' => $event
        ]);

        $this->sendEmail($subject, $to, $this->sender, $body);
    }

    /**
     * @param $subject
     * @param $to
     * @param $from
     * @param $body
     * @return void
     */
    public function sendEmail(string $subject, string $to, string $from, string $body): void
    {
        $message = Swift_Message::newInstance()
            ->setSubject($subject)
            ->setTo($to)
            ->setFrom($from)
            ->setBody($body, 'text/html');

        $this->mailer->send($message);
    }
}
