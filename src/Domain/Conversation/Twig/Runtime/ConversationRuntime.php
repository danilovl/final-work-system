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

namespace App\Domain\Conversation\Twig\Runtime;

use App\Domain\Conversation\Entity\Conversation;
use App\Domain\Conversation\Helper\ConversationHelper;
use App\Domain\Conversation\Service\ConversationService;
use App\Domain\ConversationMessage\Bus\Query\LastByConversationIds\{
    GetLastByConversationIdsQuery,
    GetLastByConversationIdsQueryResult
};
use App\Domain\ConversationMessage\Entity\ConversationMessage;
use App\Domain\User\Entity\User;
use App\Domain\Work\Entity\Work;
use App\Infrastructure\Interfaces\Bus\QueryBusInterface;
use App\Infrastructure\Service\EntityManagerService;
use Danilovl\RenderServiceTwigExtensionBundle\Attribute\AsTwigFunction;
use DateTimeImmutable;
use function Symfony\Component\String\u;

class ConversationRuntime
{
    private const int MESSAGE_PREVIEW_LENGTH = 80;

    public function __construct(
        private readonly ConversationService $conversationService,
        private readonly EntityManagerService $entityManagerService,
        private readonly QueryBusInterface $queryBus
    ) {}

    #[AsTwigFunction('check_work_users_conversation')]
    public function checkWorkUsersConversation(Work $work, User $userOne, User $userTwo): ?Conversation
    {
        return $this->conversationService->checkWorkUsersConversation($work, $userOne, $userTwo);
    }

    #[AsTwigFunction('conversation_last_message')]
    public function getLastMessage(Conversation $conversation): ?ConversationMessage
    {
        $conversationId = $conversation->getId();
        $getLastByConversationIdsQuery = GetLastByConversationIdsQuery::create([$conversationId]);

        /** @var GetLastByConversationIdsQueryResult $getLastByConversationIdsQueryResult */
        $getLastByConversationIdsQueryResult = $this->queryBus->handle($getLastByConversationIdsQuery);

        return $getLastByConversationIdsQueryResult->messages[$conversationId] ?? null;
    }

    #[AsTwigFunction('conversation_message_preview')]
    public function getMessagePreview(ConversationMessage $conversationMessage): string
    {
        $content = $conversationMessage->getContent();
        $text = strip_tags($content);
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5);

        return u($text)
            ->collapseWhitespace()
            ->truncate(self::MESSAGE_PREVIEW_LENGTH, '…', false)
            ->toString();
    }

    #[AsTwigFunction('conversation_recipient')]
    public function getRecipient(Conversation $conversation, User $user): ?User
    {
        return ConversationHelper::getRecipient($conversation, $user);
    }

    #[AsTwigFunction('conversation_message_read_date_recipient')]
    public function getMessageReadDateByRecipient(ConversationMessage $conversationMessage, User $user): ?DateTimeImmutable
    {
        /** @var Conversation $conversation */
        $conversation = $this->entityManagerService->getReference(
            Conversation::class,
            $conversationMessage->getConversation()->getId()
        );

        $recipient = ConversationHelper::getRecipient($conversation, $user);
        if ($recipient === null) {
            return null;
        }

        $recipientStatus = null;
        foreach ($conversationMessage->getStatuses() as $status) {
            if ($recipient->getId() === $status->getUser()->getId()) {
                $recipientStatus = $status;

                break;
            }
        }

        return $recipientStatus?->getUpdatedAt();
    }
}
