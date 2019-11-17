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
use FinalWork\FinalWorkBundle\Entity\SystemEventRecipient;
use FinalWork\SonataUserBundle\Entity\User;
use LogicException;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class SystemEventRecipientVoter extends Voter
{
    private const SUPPORTS = [
        VoterSupportConstant::CHANGE_VIEWED
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

        if (!$subject instanceof SystemEventRecipient) {
            return false;
        }

        return true;
    }

    /**
     * @param string $attribute
     * @param SystemEventRecipient $subject
     * @param TokenInterface $token
     * @return bool
     */
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

    /**
     * @param SystemEventRecipient $systemEventRecipient
     * @param User $user
     * @return bool
     */
    private function canChangeViewed(SystemEventRecipient $systemEventRecipient, User $user): bool
    {
        return $systemEventRecipient->isRecipient($user);
    }
}