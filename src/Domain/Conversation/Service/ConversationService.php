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

namespace App\Domain\Conversation\Service;

use App\Application\Helper\FunctionHelper;
use App\Domain\Conversation\Entity\Conversation;
use App\Domain\Conversation\Helper\ConversationHelper;
use App\Domain\User\Entity\User;
use App\Domain\Work\Entity\Work;

class ConversationService
{
    public function checkWorkUsersConversation(
        Work $work,
        User $userOne,
        User $userTwo
    ): ?Conversation {
        $workConversations = $work->getConversations();

        $conversationUserArray = [$userOne->getId(), $userTwo->getId()];
        foreach ($workConversations as $workConversation) {
            $isCompare = FunctionHelper::compareSimpleTwoArray(
                ConversationHelper::getParticipantIds($workConversation),
                $conversationUserArray
            );

            if ($isCompare) {
                return $workConversation;
            }
        }

        return null;
    }

    public function isParticipant(
        Conversation $conversation,
        User $user
    ): bool {
        return ConversationHelper::isParticipant($conversation, $user);
    }
}
