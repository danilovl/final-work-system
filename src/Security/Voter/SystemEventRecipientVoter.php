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
    SystemEventRecipient
};
use LogicException;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class SystemEventRecipientVoter extends Voter
{
    private const SUPPORTS = [
        VoterSupportConstant::CHANGE_VIEWED
    ];

    protected function supports($attribute, $subject): bool
    {
        if (!in_array($attribute, self::SUPPORTS, true)) {
            return false;
        }

        if (!$subject instanceof SystemEventRecipient) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof User) {
            return false;
        }

        switch ($attribute) {
            case VoterSupportConstant::CHANGE_VIEWED:
                return $this->canChangeViewed($subject, $user);
        }

        throw new LogicException('This code should not be reached!');
    }

    private function canChangeViewed(SystemEventRecipient $systemEventRecipient, User $user): bool
    {
        return $systemEventRecipient->isRecipient($user);
    }
}