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

namespace FinalWork\FinalWorkBundle\Services;

use FinalWork\FinalWorkBundle\Constant\SystemEventTypeConstant;
use FinalWork\FinalWorkBundle\Entity\SystemEventRecipient;
use FinalWork\FinalWorkBundle\Exception\ConstantNotFoundException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Bridge\Twig\TwigEngine;
use Twig\Environment;
use Twig\Error\{
    Error,
    LoaderError,
    SyntaxError,
    RuntimeError
};
use Twig\Extension\RuntimeExtensionInterface;

class SystemEventLinkGeneratorService implements RuntimeExtensionInterface
{
    /**
     * @var TwigEngine
     */
    private $twig;

    /**
     * @var UrlGeneratorInterface
     */
    private $urlGenerator;

    /**
     * SystemEventLinkGeneratorService constructor.
     * @param Environment $twig
     * @param UrlGeneratorInterface $urlGenerator
     */
    public function __construct(
        Environment $twig,
        UrlGeneratorInterface $urlGenerator
    ) {
        $this->twig = $twig;
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * @param SystemEventRecipient $systemEventRecipient
     * @return string
     * @throws Error
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
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
                return $this->twig->render('@FinalWork/system_event/work_create.twig', [
                    'user' => $user,
                    'work' => $work
                ]);
                break;
            case SystemEventTypeConstant::WORK_EDIT:
                return $this->twig->render('@FinalWork/system_event/work_edit.html.twig', [
                    'user' => $user,
                    'work' => $work
                ]);
                break;
            case SystemEventTypeConstant::USER_EDIT:
                return $this->twig->render('@FinalWork/system_event/user_edit.html.twig', [
                    'user' => $user
                ]);
                break;
            case SystemEventTypeConstant::TASK_CREATE:
                return $this->twig->render('@FinalWork/system_event/task_create.html.twig', [
                    'user' => $user,
                    'work' => $work,
                    'task' => $task
                ]);
                break;
            case SystemEventTypeConstant::TASK_EDIT:
                return $this->twig->render('@FinalWork/system_event/task_edit.html.twig', [
                    'user' => $user,
                    'work' => $work,
                    'task' => $task
                ]);
                break;
            case SystemEventTypeConstant::TASK_COMPLETE:
                return $this->twig->render('@FinalWork/system_event/task_complete.html.twig', [
                    'user' => $user,
                    'work' => $work,
                    'task' => $task
                ]);
                break;
            case SystemEventTypeConstant::TASK_INCOMPLETE:
                return $this->twig->render('@FinalWork/system_event/task_incomplete.html.twig', [
                    'user' => $user,
                    'work' => $work,
                    'task' => $task
                ]);
                break;
            case SystemEventTypeConstant::TASK_NOTIFY_COMPLETE:
                return $this->twig->render('@FinalWork/system_event/task_notify_complete.html.twig', [
                    'user' => $user,
                    'work' => $work,
                    'task' => $task
                ]);
                break;
            case SystemEventTypeConstant::TASK_NOTIFY_INCOMPLETE:
                return $this->twig->render('@FinalWork/system_event/task_notify_incomplete.html.twig', [
                    'user' => $user,
                    'work' => $work,
                    'task' => $task
                ]);
                break;
            case SystemEventTypeConstant::VERSION_CREATE:
                return $this->twig->render('@FinalWork/system_event/version_create.twig', [
                    'user' => $user,
                    'work' => $work,
                    'version' => $version
                ]);
                break;
            case SystemEventTypeConstant::VERSION_EDIT:
                return $this->twig->render('@FinalWork/system_event/version_edit.twig', [
                    'user' => $user,
                    'work' => $work,
                    'version' => $version
                ]);
                break;
            case SystemEventTypeConstant::DOCUMENT_CREATE:
                return $this->twig->render('@FinalWork/system_event/document_create.html.twig', [
                    'user' => $user,
                    'work' => $work,
                    'document' => $document
                ]);
                break;
            case SystemEventTypeConstant::MESSAGE_CREATE:
                return $this->twig->render('@FinalWork/system_event/message_create.html.twig', [
                    'user' => $user,
                    'conversation' => $conversation
                ]);
                break;
            case SystemEventTypeConstant::EVENT_CREATE:
                return $this->twig->render('@FinalWork/system_event/event_create.html.twig', [
                    'user' => $user,
                    'event' => $event
                ]);
                break;
            case SystemEventTypeConstant::EVENT_EDIT:
                return $this->twig->render('@FinalWork/system_event/event_edit.html.twig', [
                    'user' => $user,
                    'event' => $event
                ]);
                break;
            case SystemEventTypeConstant::EVENT_SWITCH_SKYPE:
                return $this->twig->render('@FinalWork/system_event/event_switch_skype.html.twig', [
                    'user' => $user,
                    'event' => $event
                ]);
                break;
            case SystemEventTypeConstant::EVENT_COMMENT_CREATE:
                return $this->twig->render('@FinalWork/system_event/event_comment_create.html.twig', [
                    'user' => $user,
                    'event' => $event
                ]);
                break;
            case SystemEventTypeConstant::EVENT_COMMENT_EDIT:
                return $this->twig->render('@FinalWork/system_event/event_comment_edit.html.twig', [
                    'user' => $user,
                    'event' => $event
                ]);
                break;
            default:
                throw new ConstantNotFoundException('Event type constant not found');
        }
    }
}
