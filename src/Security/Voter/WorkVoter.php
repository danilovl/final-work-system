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
    Work
};
use LogicException;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class WorkVoter extends Voter
{
    private const SUPPORTS = [
        VoterSupportConstant::VIEW,
        VoterSupportConstant::EDIT,
        VoterSupportConstant::DELETE
    ];

    protected function supports($attribute, $subject): bool
    {
        if (!in_array($attribute, self::SUPPORTS, true)) {
            return false;
        }

        if (!$subject instanceof Work) {
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
            case VoterSupportConstant::VIEW:
                return $this->canView($subject, $user);
            case VoterSupportConstant::EDIT:
                return $this->canEdit($subject, $user);
            case VoterSupportConstant::DELETE:
                return $this->canDelete($subject, $user);
        }

        throw new LogicException('This code should not be reached!');
    }

    private function canView(Work $work, User $user): bool
    {
        return $work->isParticipant($user);
    }

    private function canEdit(Work $work, User $user): bool
    {
        return $work->isSupervisor($user);
    }

    private function canDelete(Work $work, User $user): bool
    {
        return $this->canEdit($work, $user);
    }
}