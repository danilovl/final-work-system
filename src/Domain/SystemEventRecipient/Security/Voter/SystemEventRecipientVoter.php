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

namespace App\Domain\SystemEventRecipient\Security\Voter;

use App\Application\Constant\VoterSupportConstant;
use App\Domain\SystemEventRecipient\Entity\SystemEventRecipient;
use App\Domain\User\Entity\User;
use LogicException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class SystemEventRecipientVoter extends Voter
{
    private const SUPPORTS = [
        VoterSupportConstant::CHANGE_VIEWED->value
    ];

    protected function supports(string $attribute, mixed $subject): bool
    {
        if (!in_array($attribute, self::SUPPORTS, true)) {
            return false;
        }

        if (!$subject instanceof SystemEventRecipient) {
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

        if ($attribute == VoterSupportConstant::CHANGE_VIEWED->value) {
            return $this->canChangeViewed($subject, $user);
        }

        throw new LogicException('This code should not be reached!');
    }

    private function canChangeViewed(SystemEventRecipient $systemEventRecipient, User $user): bool
    {
        return $systemEventRecipient->isRecipient($user);
    }
}