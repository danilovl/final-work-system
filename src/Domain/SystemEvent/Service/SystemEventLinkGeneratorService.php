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

use App\Application\Constant\{
    LocaleConstant,
    SystemEventTypeConstant
};
use App\Application\Exception\{
    InvalidArgumentException,
    ConstantNotFoundException
};
use App\Domain\SystemEvent\Constant\SystemEventGeneratorFolderConstant;
use App\Domain\SystemEvent\Entity\SystemEvent;
use App\Domain\SystemEventRecipient\Entity\SystemEventRecipient;
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

        $locale = LocaleConstant::ISO_CS ?? $this->requestStack->getCurrentRequest()->getLocale();
        $folder = sprintf('system_event/%s/%s', $type, $locale);

        return match ($systemEventTypeId) {
            SystemEventTypeConstant::WORK_CREATE => $folder . '/work_create.twig',
            SystemEventTypeConstant::WORK_EDIT => $folder . '/work_edit.html.twig',
            SystemEventTypeConstant::USER_EDIT => $folder . '/user_edit.html.twig',
            SystemEventTypeConstant::TASK_CREATE => $folder . '/task_create.html.twig',
            SystemEventTypeConstant::TASK_EDIT => $folder . '/task_edit.html.twig',
            SystemEventTypeConstant::TASK_COMPLETE => $folder . '/task_complete.html.twig',
            SystemEventTypeConstant::TASK_INCOMPLETE => $folder . '/task_incomplete.html.twig',
            SystemEventTypeConstant::TASK_NOTIFY_COMPLETE => $folder . '/task_notify_complete.html.twig',
            SystemEventTypeConstant::TASK_NOTIFY_INCOMPLETE => $folder . '/task_notify_incomplete.html.twig',
            SystemEventTypeConstant::TASK_REMIND_DEADLINE => $folder . '/task_remind_deadline.html.twig',
            SystemEventTypeConstant::VERSION_CREATE => $folder . '/version_create.twig',
            SystemEventTypeConstant::VERSION_EDIT => $folder . '/version_edit.twig',
            SystemEventTypeConstant::DOCUMENT_CREATE => $folder . '/document_create.html.twig',
            SystemEventTypeConstant::MESSAGE_CREATE => $folder . '/message_create.html.twig',
            SystemEventTypeConstant::EVENT_CREATE => $folder . '/event_create.html.twig',
            SystemEventTypeConstant::EVENT_EDIT => $folder . '/event_edit.html.twig',
            SystemEventTypeConstant::EVENT_SWITCH_SKYPE => $folder . '/event_switch_skype.html.twig',
            SystemEventTypeConstant::EVENT_COMMENT_CREATE => $folder . '/event_comment_create.html.twig',
            SystemEventTypeConstant::EVENT_COMMENT_EDIT => $folder . '/event_comment_edit.html.twig',
            default => throw new ConstantNotFoundException('Event type constant not found'),
        };
    }
}
