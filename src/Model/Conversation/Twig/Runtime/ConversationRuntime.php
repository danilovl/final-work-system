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

namespace App\Model\Conversation\Twig\Runtime;

use App\Model\Conversation\Service\ConversationService;
use DateTime;
use App\Entity\{
    User,
    Work,
    Conversation,
    ConversationMessage
};
use Twig\Extension\AbstractExtension;

class ConversationRuntime extends AbstractExtension
{
    public function __construct(private ConversationService $conversationService)
    {
    }

    public function checkWorkUsersConversation(
        Work $work,
        User $userOne,
        User $userTwo
    ): ?Conversation {
        return $this->conversationService->checkWorkUsersConversation($work, $userOne, $userTwo);
    }

    public function getLastMessage(Conversation $conversation): ?ConversationMessage
    {
        return $this->conversationService->getLastMessage($conversation);
    }

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
