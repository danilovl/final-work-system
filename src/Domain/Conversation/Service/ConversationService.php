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
use App\Domain\ConversationMessage\Entity\ConversationMessage;
use App\Domain\User\Entity\User;
use App\Domain\Work\Entity\Work;
use Doctrine\Common\Collections\Criteria;

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
        $participants = $conversation->getParticipants();

        foreach ($participants as $participant) {
            if ($participant->getUser()->getId() === $user->getId()) {
                return true;
            }
        }

        return false;
    }

    public function getLastMessage(Conversation $conversation): ?ConversationMessage
    {
        $messages = $conversation->getMessages();

        if ($messages->count() === 0) {
            return null;
        }

        $criteria = Criteria::create()
            ->orderBy([
                'createdAt' => Criteria::DESC
            ])
            ->setMaxResults(1);

        /** @var ConversationMessage|null $message */
        $message = $messages->matching($criteria)[0] ?? null;

        return $message;
    }
}
