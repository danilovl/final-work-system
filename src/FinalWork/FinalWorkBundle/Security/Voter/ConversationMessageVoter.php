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

namespace FinalWork\FinalWorkBundle\Security\Voter;

use FinalWork\FinalWorkBundle\Constant\VoterSupportConstant;
use FinalWork\FinalWorkBundle\Entity\ConversationMessage;
use FinalWork\SonataUserBundle\Entity\User;
use LogicException;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class ConversationMessageVoter extends Voter
{
    private const SUPPORTS = [
        VoterSupportConstant::CHANGE_READ_MESSAGE_STATUS
    ];

    /**
     * @param string $attribute
     * @param mixed $subject
     * @return bool
     */
    protected function supports($attribute, $subject): bool
    {
        if (!in_array($attribute, self::SUPPORTS, true)) {
            return false;
        }

        if (!$subject instanceof ConversationMessage) {
            return false;
        }

        return true;
    }

    /**
     * @param string $attribute
     * @param ConversationMessage $subject
     * @param TokenInterface $token
     * @return bool
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token): bool
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

    /**
     * @param ConversationMessage $conversationMessage
     * @param User $user
     * @return bool
     */
    private function changeReadMessageStatus(ConversationMessage $conversationMessage, User $user): bool
    {
        $conversation = $conversationMessage->getConversation();

        return $conversation->isParticipant($user) && !$conversationMessage->isOwner($user);
    }
}