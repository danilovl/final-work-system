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

namespace App\Twig\Runtime;

use App\Service\Conversation\ConversationService;
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
}

