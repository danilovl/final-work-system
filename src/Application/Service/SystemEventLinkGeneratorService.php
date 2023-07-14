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

namespace App\Application\Service;

use App\Application\Exception\ConstantNotFoundException;
use App\Domain\SystemEvent\Entity\SystemEvent;
use App\Domain\SystemEventRecipient\Entity\SystemEventRecipient;
use App\Domain\SystemEventType\Constant\SystemEventTypeConstant;
use Twig\Environment;
use Twig\Extension\RuntimeExtensionInterface;

class SystemEventLinkGeneratorService implements RuntimeExtensionInterface
{
    public function __construct(private Environment $twig)
    {
    }

    public function generateLink(SystemEventRecipient $systemEventRecipient): string
    {
        $systemEvent = $systemEventRecipient->getSystemEvent();

        return $this->twig->render(
            $this->getTemplate($systemEvent->getType()->getId()),
            $this->getTemplateParameters($systemEvent)
        );
    }

    private function getTemplateParameters(SystemEvent $systemEvent): array
    {
        return [
            'user' => $systemEvent->getOwner(),
            'work' => $systemEvent->getWork(),
            'task' => $systemEvent->getTask(),
            'version' => $systemEvent->getMedia(),
            'document' => $systemEvent->getMedia(),
            'conversation' => $systemEvent->getConversation(),
            'event' => $systemEvent->getEvent()
        ];
    }

    private function getTemplate(int $systemEventTypeId): string
    {
        return match ($systemEventTypeId) {
            SystemEventTypeConstant::WORK_CREATE => 'system_event/work_create.twig',
            SystemEventTypeConstant::WORK_EDIT => 'system_event/work_edit.html.twig',
            SystemEventTypeConstant::USER_EDIT => 'system_event/user_edit.html.twig',
            SystemEventTypeConstant::TASK_CREATE => 'system_event/task_create.html.twig',
            SystemEventTypeConstant::TASK_EDIT => 'system_event/task_edit.html.twig',
            SystemEventTypeConstant::TASK_COMPLETE => 'system_event/task_complete.html.twig',
            SystemEventTypeConstant::TASK_INCOMPLETE => 'system_event/task_incomplete.html.twig',
            SystemEventTypeConstant::TASK_NOTIFY_COMPLETE => 'system_event/task_notify_complete.html.twig',
            SystemEventTypeConstant::TASK_NOTIFY_INCOMPLETE => 'system_event/task_notify_incomplete.html.twig',
            SystemEventTypeConstant::TASK_REMIND_DEADLINE => 'system_event/task_remind_deadline.html.twig',
            SystemEventTypeConstant::VERSION_CREATE => 'system_event/version_create.twig',
            SystemEventTypeConstant::VERSION_EDIT => 'system_event/version_edit.twig',
            SystemEventTypeConstant::DOCUMENT_CREATE => 'system_event/document_create.html.twig',
            SystemEventTypeConstant::MESSAGE_CREATE => 'system_event/message_create.html.twig',
            SystemEventTypeConstant::EVENT_CREATE => 'system_event/event_create.html.twig',
            SystemEventTypeConstant::EVENT_EDIT => 'system_event/event_edit.html.twig',
            SystemEventTypeConstant::EVENT_SWITCH_SKYPE => 'system_event/event_switch_skype.html.twig',
            SystemEventTypeConstant::EVENT_COMMENT_CREATE => 'system_event/event_comment_create.html.twig',
            SystemEventTypeConstant::EVENT_COMMENT_EDIT => 'system_event/event_comment_edit.html.twig',
            default => throw new ConstantNotFoundException('Event type constant not found'),
        };
    }
}
