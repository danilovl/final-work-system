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

namespace App\Security\Voter;

use App\Constant\VoterSupportConstant;
use App\Entity\{
    User,
    Conversation
};
use LogicException;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class ConversationVoter extends Voter
{
    private const SUPPORTS = [
        VoterSupportConstant::VIEW,
        VoterSupportConstant::DELETE
    ];

    protected function supports(string $attribute, mixed $subject): bool
    {
        if (!in_array($attribute, self::SUPPORTS, true)) {
            return false;
        }

        if (!$subject instanceof Conversation) {
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

        switch ($attribute) {
            case VoterSupportConstant::VIEW:
                return $this->canView($subject, $user);
            case VoterSupportConstant::DELETE:
                return $this->canDelete($subject, $user);
        }

        throw new LogicException('This code should not be reached!');
    }

    private function canView(Conversation $conversation, User $user): bool
    {
        return $conversation->isParticipant($user);
    }

    private function canDelete(Conversation $conversation, User $user): bool
    {
        return $conversation->isOwner($user) || $conversation->getWork()->getSupervisor()->getId() === $user->getId();
    }
}