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

namespace App\Services;

use App\Constant\SystemEventTypeConstant;
use App\Entity\SystemEventRecipient;
use App\Exception\ConstantNotFoundException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;
use Twig\Extension\RuntimeExtensionInterface;

class SystemEventLinkGeneratorService implements RuntimeExtensionInterface
{
    private Environment $twig;
    private UrlGeneratorInterface $urlGenerator;

    public function __construct(
        Environment $twig,
        UrlGeneratorInterface $urlGenerator
    ) {
        $this->twig = $twig;
        $this->urlGenerator = $urlGenerator;
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

        switch ($systemEventRecipient->getSystemEvent()->getType()->getId()) {
            case SystemEventTypeConstant::WORK_CREATE:
                return $this->twig->render('system_event/work_create.twig', [
                    'user' => $user,
                    'work' => $work
                ]);
            case SystemEventTypeConstant::WORK_EDIT:
                return $this->twig->render('system_event/work_edit.html.twig', [
                    'user' => $user,
                    'work' => $work
                ]);
            case SystemEventTypeConstant::USER_EDIT:
                return $this->twig->render('system_event/user_edit.html.twig', [
                    'user' => $user
                ]);
            case SystemEventTypeConstant::TASK_CREATE:
                return $this->twig->render('system_event/task_create.html.twig', [
                    'user' => $user,
                    'work' => $work,
                    'task' => $task
                ]);
            case SystemEventTypeConstant::TASK_EDIT:
                return $this->twig->render('system_event/task_edit.html.twig', [
                    'user' => $user,
                    'work' => $work,
                    'task' => $task
                ]);
            case SystemEventTypeConstant::TASK_COMPLETE:
                return $this->twig->render('system_event/task_complete.html.twig', [
                    'user' => $user,
                    'work' => $work,
                    'task' => $task
                ]);
            case SystemEventTypeConstant::TASK_INCOMPLETE:
                return $this->twig->render('system_event/task_incomplete.html.twig', [
                    'user' => $user,
                    'work' => $work,
                    'task' => $task
                ]);
            case SystemEventTypeConstant::TASK_NOTIFY_COMPLETE:
                return $this->twig->render('system_event/task_notify_complete.html.twig', [
                    'user' => $user,
                    'work' => $work,
                    'task' => $task
                ]);
            case SystemEventTypeConstant::TASK_NOTIFY_INCOMPLETE:
                return $this->twig->render('system_event/task_notify_incomplete.html.twig', [
                    'user' => $user,
                    'work' => $work,
                    'task' => $task
                ]);
            case SystemEventTypeConstant::TASK_REMIND_DEADLINE:
                return $this->twig->render('system_event/task_remind_deadline.html.twig', [
                    'user' => $user,
                    'work' => $work,
                    'task' => $task
                ]);
            case SystemEventTypeConstant::VERSION_CREATE:
                return $this->twig->render('system_event/version_create.twig', [
                    'user' => $user,
                    'work' => $work,
                    'version' => $version
                ]);
            case SystemEventTypeConstant::VERSION_EDIT:
                return $this->twig->render('system_event/version_edit.twig', [
                    'user' => $user,
                    'work' => $work,
                    'version' => $version
                ]);
            case SystemEventTypeConstant::DOCUMENT_CREATE:
                return $this->twig->render('system_event/document_create.html.twig', [
                    'user' => $user,
                    'work' => $work,
                    'document' => $document
                ]);
            case SystemEventTypeConstant::MESSAGE_CREATE:
                return $this->twig->render('system_event/message_create.html.twig', [
                    'user' => $user,
                    'conversation' => $conversation
                ]);
            case SystemEventTypeConstant::EVENT_CREATE:
                return $this->twig->render('system_event/event_create.html.twig', [
                    'user' => $user,
                    'event' => $event
                ]);
            case SystemEventTypeConstant::EVENT_EDIT:
                return $this->twig->render('system_event/event_edit.html.twig', [
                    'user' => $user,
                    'event' => $event
                ]);
            case SystemEventTypeConstant::EVENT_SWITCH_SKYPE:
                return $this->twig->render('system_event/event_switch_skype.html.twig', [
                    'user' => $user,
                    'event' => $event
                ]);
            case SystemEventTypeConstant::EVENT_COMMENT_CREATE:
                return $this->twig->render('system_event/event_comment_create.html.twig', [
                    'user' => $user,
                    'event' => $event
                ]);
            case SystemEventTypeConstant::EVENT_COMMENT_EDIT:
                return $this->twig->render('system_event/event_comment_edit.html.twig', [
                    'user' => $user,
                    'event' => $event
                ]);
            default:
                throw new ConstantNotFoundException('Event type constant not found');
        }
    }
}
