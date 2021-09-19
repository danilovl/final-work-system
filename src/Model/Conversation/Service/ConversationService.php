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

namespace App\Model\Conversation\Service;

use Doctrine\Common\Collections\Criteria;
use App\Helper\{
    FunctionHelper,
    ConversationHelper
};
use App\Entity\{
    User,
    Work,
    Conversation,
    ConversationMessage
};

class ConversationService
{
    public function checkWorkUsersConversation(
        Work $work,
        User $userOne,
        User $userTwo
    ): ?Conversation {
        $workConversations = $work->getConversations();

        $conversationUserArray = [$userOne->getId(), $userTwo->getId()];
        if ($workConversations) {
            /** @var Conversation $workConversation */
            foreach ($workConversations as $workConversation) {
                $isCompare = FunctionHelper::compareSimpleTwoArray(
                    ConversationHelper::getParticipantIds($workConversation),
                    $conversationUserArray
                );

                if ($isCompare) {
                    return $workConversation;
                }
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

        if ($messages->count() > 0) {
            $criteria = Criteria::create()
                ->orderBy([
                    'createdAt' => Criteria::DESC
                ])
                ->setMaxResults(1);

            return $messages->matching($criteria)[0];
        }

        return null;
    }
}
