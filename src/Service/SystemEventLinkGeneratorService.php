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

namespace App\Service;

use App\Constant\SystemEventTypeConstant;
use App\Entity\SystemEventRecipient;
use App\Exception\ConstantNotFoundException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;
use Twig\Extension\RuntimeExtensionInterface;

class SystemEventLinkGeneratorService implements RuntimeExtensionInterface
{
    public function __construct(
        private Environment $twig,
        private UrlGeneratorInterface $urlGenerator
    ) {
    }

    public function generateLink(SystemEventRecipient $systemEventRecipient): string
    {
        $user = $systemEventRecipient->getSystemEvent()->getOwner();
        $work = $systemEventRecipient->getSystemEvent()->getWork();
        $task = $systemEventRecipient->getSystemEvent()->getTask();
        $version = $systemEventRecipient->getSystemEvent()->getMedia();
        $document = $systemEventRecipient->getSystemEvent()->getMedia();
        $conversation = $systemEventRecipient->getSystemEvent()->getConversation();
        $event = $systemEventRecipient->getSystemEvent()->getEvent();

        $link =  match ($systemEventRecipient->getSystemEvent()->getType()->getId()) {
            SystemEventTypeConstant::WORK_CREATE => $this->twig->render('system_event/work_create.twig', [
                'user' => $user,
                'work' => $work
            ]),
            SystemEventTypeConstant::WORK_EDIT => $this->twig->render('system_event/work_edit.html.twig', [
                'user' => $user,
                'work' => $work
            ]),
            SystemEventTypeConstant::USER_EDIT => $this->twig->render('system_event/user_edit.html.twig', [
                'user' => $user
            ]),
            SystemEventTypeConstant::TASK_CREATE => $this->twig->render('system_event/task_create.html.twig', [
                'user' => $user,
                'work' => $work,
                'task' => $task
            ]),
            SystemEventTypeConstant::TASK_EDIT => $this->twig->render('system_event/task_edit.html.twig', [
                'user' => $user,
                'work' => $work,
                'task' => $task
            ]),
            SystemEventTypeConstant::TASK_COMPLETE => $this->twig->render('system_event/task_complete.html.twig', [
                'user' => $user,
                'work' => $work,
                'task' => $task
            ]),
            SystemEventTypeConstant::TASK_INCOMPLETE => $this->twig->render('system_event/task_incomplete.html.twig', [
                'user' => $user,
                'work' => $work,
                'task' => $task
            ]),
            SystemEventTypeConstant::TASK_NOTIFY_COMPLETE => $this->twig->render('system_event/task_notify_complete.html.twig', [
                'user' => $user,
                'work' => $work,
                'task' => $task
            ]),
            SystemEventTypeConstant::TASK_NOTIFY_INCOMPLETE => $this->twig->render('system_event/task_notify_incomplete.html.twig', [
                'user' => $user,
                'work' => $work,
                'task' => $task
            ]),
            SystemEventTypeConstant::TASK_REMIND_DEADLINE => $this->twig->render('system_event/task_remind_deadline.html.twig', [
                'user' => $user,
                'work' => $work,
                'task' => $task
            ]),
            SystemEventTypeConstant::VERSION_CREATE => $this->twig->render('system_event/version_create.twig', [
                'user' => $user,
                'work' => $work,
                'version' => $version
            ]),
            SystemEventTypeConstant::VERSION_EDIT => $this->twig->render('system_event/version_edit.twig', [
                'user' => $user,
                'work' => $work,
                'version' => $version
            ]),
            SystemEventTypeConstant::DOCUMENT_CREATE => $this->twig->render('system_event/document_create.html.twig', [
                'user' => $user,
                'work' => $work,
                'document' => $document
            ]),
            SystemEventTypeConstant::MESSAGE_CREATE => $this->twig->render('system_event/message_create.html.twig', [
                'user' => $user,
                'conversation' => $conversation
            ]),
            SystemEventTypeConstant::EVENT_CREATE => $this->twig->render('system_event/event_create.html.twig', [
                'user' => $user,
                'event' => $event
            ]),
            SystemEventTypeConstant::EVENT_EDIT => $this->twig->render('system_event/event_edit.html.twig', [
                'user' => $user,
                'event' => $event
            ]),
            SystemEventTypeConstant::EVENT_SWITCH_SKYPE => $this->twig->render('system_event/event_switch_skype.html.twig', [
                'user' => $user,
                'event' => $event
            ]),
            SystemEventTypeConstant::EVENT_COMMENT_CREATE => $this->twig->render('system_event/event_comment_create.html.twig', [
                'user' => $user,
                'event' => $event
            ]),
            SystemEventTypeConstant::EVENT_COMMENT_EDIT => $this->twig->render('system_event/event_comment_edit.html.twig', [
                'user' => $user,
                'event' => $event
            ]),
            default => throw new ConstantNotFoundException('Event type constant not found'),
        };

        return $link;
    }
}
