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

use App\Infrastructure\Exception\ConstantNotFoundException;
use App\Domain\SystemEvent\Entity\SystemEvent;
use App\Domain\SystemEventRecipient\Entity\SystemEventRecipient;
use App\Domain\SystemEventType\Constant\SystemEventTypeConstant;
use Danilovl\HashidsBundle\Interfaces\HashidsServiceInterface;
use Danilovl\RenderServiceTwigExtensionBundle\Attribute\AsTwigFunction;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

readonly class SystemEventLinkGeneratorService
{
    private const string TRANSLATION_PREFIX = 'app.system_event.';

    public function __construct(
        private RequestStack $requestStack,
        private TranslatorInterface $translator,
        private UrlGeneratorInterface $urlGenerator,
        private HashidsServiceInterface $hashidsService
    ) {}

    #[AsTwigFunction('system_event_generate_link')]
    public function generateLink(SystemEventRecipient $systemEventRecipient): string
    {
        return $this->generate($systemEventRecipient, true);
    }

    public function generateText(SystemEventRecipient $systemEventRecipient): string
    {
        return $this->generate($systemEventRecipient, false);
    }

    private function generate(SystemEventRecipient $systemEventRecipient, bool $withLink): string
    {
        $systemEvent = $systemEventRecipient->getSystemEvent();
        $key = $this->getTranslationKey($systemEvent->getType()->getId());
        $locale = $this->requestStack->getCurrentRequest()?->getLocale() ?? $systemEventRecipient->getRecipient()->getLocale();

        $parameters = [
            '%user%' => '<b>' . $this->escape((string) $systemEvent->getOwner()) . '</b>',
            '%task%' => $this->escape((string) $systemEvent->getTask()),
            '%version%' => $this->escape((string) $systemEvent->getMedia()),
            '%document%' => $this->escape((string) $systemEvent->getMedia()),
            '%link%' => $this->createTarget($systemEvent, $key, $withLink, $locale)
        ];

        $messageId = self::TRANSLATION_PREFIX . $key . '.message';

        return $this->translator->trans($messageId, $parameters, null, $locale);
    }

    private function createTarget(SystemEvent $systemEvent, string $key, bool $withLink, ?string $locale): string
    {
        $targetId = self::TRANSLATION_PREFIX . $key . '.target';
        $target = $this->escape($this->translator->trans($targetId, [], null, $locale));
        $work = $systemEvent->getWork();
        $isWorkTarget = !in_array($key, ['document_create', 'message_create'], true) && !str_starts_with($key, 'event_');

        if (!$withLink) {
            if ($work === null || !$isWorkTarget) {
                return $target;
            }

            return $target . ' "' . $this->escape($work->getTitle()) . '"';
        }

        $url = $this->createUrl($systemEvent, $key);
        if ($url === null) {
            return $target;
        }

        return '<a class="titleToolTip blue" target="_blank" href="' . $this->escape($url) . '">' . $target . '</a>';
    }

    private function createUrl(SystemEvent $systemEvent, string $key): ?string
    {
        $conversation = $systemEvent->getConversation();
        $event = $systemEvent->getEvent();
        $work = $systemEvent->getWork();
        $isEventType = str_starts_with($key, 'event_');

        return match (true) {
            $key === 'document_create' => $this->urlGenerator->generate('document_list'),
            $key === 'message_create' && $conversation !== null => $this->createEntityUrl('conversation_detail', $conversation->getId()),
            $isEventType && $event !== null => $this->createEntityUrl('event_detail', $event->getId()),
            $work !== null => $this->createEntityUrl('work_detail', $work->getId()),
            default => null
        };
    }

    private function createEntityUrl(string $route, int $id): string
    {
        $encodedId = $this->hashidsService->encode($id);

        return $this->urlGenerator->generate($route, ['id' => $encodedId]);
    }

    private function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    private function getTranslationKey(int $systemEventTypeId): string
    {
        return match ($systemEventTypeId) {
            SystemEventTypeConstant::WORK_CREATE->value => 'work_create',
            SystemEventTypeConstant::WORK_EDIT->value => 'work_edit',
            SystemEventTypeConstant::WORK_REMIND_DEADLINE->value => 'work_remind_deadline',
            SystemEventTypeConstant::USER_EDIT->value => 'user_edit',
            SystemEventTypeConstant::TASK_CREATE->value => 'task_create',
            SystemEventTypeConstant::TASK_EDIT->value => 'task_edit',
            SystemEventTypeConstant::TASK_COMPLETE->value => 'task_complete',
            SystemEventTypeConstant::TASK_INCOMPLETE->value => 'task_incomplete',
            SystemEventTypeConstant::TASK_NOTIFY_COMPLETE->value => 'task_notify_complete',
            SystemEventTypeConstant::TASK_NOTIFY_INCOMPLETE->value => 'task_notify_incomplete',
            SystemEventTypeConstant::TASK_REMIND_DEADLINE->value => 'task_remind_deadline',
            SystemEventTypeConstant::VERSION_CREATE->value => 'version_create',
            SystemEventTypeConstant::VERSION_EDIT->value => 'version_edit',
            SystemEventTypeConstant::DOCUMENT_CREATE->value => 'document_create',
            SystemEventTypeConstant::MESSAGE_CREATE->value => 'message_create',
            SystemEventTypeConstant::EVENT_CREATE->value => 'event_create',
            SystemEventTypeConstant::EVENT_EDIT->value => 'event_edit',
            SystemEventTypeConstant::EVENT_SWITCH_SKYPE->value => 'event_switch_skype',
            SystemEventTypeConstant::EVENT_COMMENT_CREATE->value => 'event_comment_create',
            SystemEventTypeConstant::EVENT_COMMENT_EDIT->value => 'event_comment_edit',
            default => throw new ConstantNotFoundException('Event type constant not found')
        };
    }
}
