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

namespace App\Domain\SystemEvent\Service;

use App\Application\Constant\LocaleConstant;
use App\Application\Exception\{
    ConstantNotFoundException,
    InvalidArgumentException
};
use App\Domain\SystemEvent\Constant\SystemEventGeneratorFolderConstant;
use App\Domain\SystemEvent\Entity\SystemEvent;
use App\Domain\SystemEventRecipient\Entity\SystemEventRecipient;
use App\Domain\SystemEventType\Constant\SystemEventTypeConstant;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Environment;
use Twig\Extension\RuntimeExtensionInterface;

readonly class SystemEventLinkGeneratorService implements RuntimeExtensionInterface
{
    public function __construct(
        private RequestStack $requestStack,
        private Environment $twig
    ) {}

    public function generateLink(SystemEventRecipient $systemEventRecipient): string
    {
        return $this->generateString(SystemEventGeneratorFolderConstant::LINK, $systemEventRecipient);
    }

    public function generateText(SystemEventRecipient $systemEventRecipient): string
    {
        return $this->generateString(SystemEventGeneratorFolderConstant::TEXT, $systemEventRecipient);
    }

    private function generateString(string $type, SystemEventRecipient $systemEventRecipient): string
    {
        $systemEvent = $systemEventRecipient->getSystemEvent();

        return $this->twig->render(
            $this->getTemplate($type, $systemEvent->getType()->getId()),
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

    private function getTemplate(string $type, int $systemEventTypeId): string
    {
        if (!in_array($type, SystemEventGeneratorFolderConstant::FOLDERS, true)) {
            throw new InvalidArgumentException(sprintf('Folder "%s" for generator not exist', $type));
        }

        /** TODO: Now available templates only for cs $locale */
        $locale = LocaleConstant::ISO_CS->value ?? $this->requestStack->getCurrentRequest()?->getLocale();
        $folder = sprintf('domain/system_event/%s/%s', $type, $locale);

        return match ($systemEventTypeId) {
            SystemEventTypeConstant::WORK_CREATE->value => $folder . '/work_create.twig',
            SystemEventTypeConstant::WORK_EDIT->value => $folder . '/work_edit.html.twig',
            SystemEventTypeConstant::WORK_REMIND_DEADLINE->value => $folder . '/work_remind_deadline.html.twig',
            SystemEventTypeConstant::USER_EDIT->value => $folder . '/user_edit.html.twig',
            SystemEventTypeConstant::TASK_CREATE->value => $folder . '/task_create.html.twig',
            SystemEventTypeConstant::TASK_EDIT->value => $folder . '/task_edit.html.twig',
            SystemEventTypeConstant::TASK_COMPLETE->value => $folder . '/task_complete.html.twig',
            SystemEventTypeConstant::TASK_INCOMPLETE->value => $folder . '/task_incomplete.html.twig',
            SystemEventTypeConstant::TASK_NOTIFY_COMPLETE->value => $folder . '/task_notify_complete.html.twig',
            SystemEventTypeConstant::TASK_NOTIFY_INCOMPLETE->value => $folder . '/task_notify_incomplete.html.twig',
            SystemEventTypeConstant::TASK_REMIND_DEADLINE->value => $folder . '/task_remind_deadline.html.twig',
            SystemEventTypeConstant::VERSION_CREATE->value => $folder . '/version_create.twig',
            SystemEventTypeConstant::VERSION_EDIT->value => $folder . '/version_edit.twig',
            SystemEventTypeConstant::DOCUMENT_CREATE->value => $folder . '/document_create.html.twig',
            SystemEventTypeConstant::MESSAGE_CREATE->value => $folder . '/message_create.html.twig',
            SystemEventTypeConstant::EVENT_CREATE->value => $folder . '/event_create.html.twig',
            SystemEventTypeConstant::EVENT_EDIT->value => $folder . '/event_edit.html.twig',
            SystemEventTypeConstant::EVENT_SWITCH_SKYPE->value => $folder . '/event_switch_skype.html.twig',
            SystemEventTypeConstant::EVENT_COMMENT_CREATE->value => $folder . '/event_comment_create.html.twig',
            SystemEventTypeConstant::EVENT_COMMENT_EDIT->value => $folder . '/event_comment_edit.html.twig',
            default => throw new ConstantNotFoundException('Event type constant not found'),
        };
    }
}
