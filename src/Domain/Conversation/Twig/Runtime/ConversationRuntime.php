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
use App\Domain\Conversation\Service\ConversationService;
use App\Domain\ConversationMessage\Entity\ConversationMessage;
use App\Domain\User\Entity\User;
use App\Domain\Work\Entity\Work;
use Danilovl\RenderServiceTwigExtensionBundle\Attribute\AsTwigFunction;
use DateTime;

class ConversationRuntime
{
    public function __construct(private readonly ConversationService $conversationService) {}

    #[AsTwigFunction('check_work_users_conversation')]
    public function checkWorkUsersConversation(Work $work, User $userOne, User $userTwo): ?Conversation
    {
        return $this->conversationService->checkWorkUsersConversation($work, $userOne, $userTwo);
    }

    #[AsTwigFunction('conversation_last_message')]
    public function getLastMessage(Conversation $conversation): ?ConversationMessage
    {
        return $this->conversationService->getLastMessage($conversation);
    }

    #[AsTwigFunction('conversation_message_read_date_recipient')]
    public function getMessageReadDateByRecipient(ConversationMessage $conversationMessage): ?DateTime
    {
        $recipient = $conversationMessage->getConversation()->getRecipient();
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
