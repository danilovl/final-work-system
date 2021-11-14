<?php declare(strict_types=1);

/*
 *
 * This file is part of the FinalWorkSystem project.
 * (c) Vladimir Danilov
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace App\Model\ConversationMessage\Security\Voter;

use App\Constant\VoterSupportConstant;
use App\Model\Conversation\Service\ConversationService;
use App\Entity\{
    User,
    ConversationMessage
};
use LogicException;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class ConversationMessageVoter extends Voter
{
    private const SUPPORTS = [
        VoterSupportConstant::CHANGE_READ_MESSAGE_STATUS
    ];

    public function __construct(private ConversationService $conversationService)
    {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        if (!in_array($attribute, self::SUPPORTS, true)) {
            return false;
        }

        if (!$subject instanceof ConversationMessage) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof User) {
            return false;
        }

        if ($attribute === VoterSupportConstant::CHANGE_READ_MESSAGE_STATUS) {
            return $this->changeReadMessageStatus($subject, $user);
        }

        throw new LogicException('This code should not be reached!');
    }

    private function changeReadMessageStatus(ConversationMessage $conversationMessage, User $user): bool
    {
        $conversation = $conversationMessage->getConversation();

        return $this->conversationService->isParticipant($conversation, $user) && !$conversationMessage->isOwner($user);
    }
}