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

namespace App\Domain\EventSchedule\Security\Voter;

use App\Application\Constant\VoterSupportConstant;
use App\Domain\EventSchedule\Entity\EventSchedule;
use App\Domain\User\Entity\User;
use LogicException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class EventScheduleVoter extends Voter
{
    public const SUPPORTS = [
        VoterSupportConstant::VIEW->value,
        VoterSupportConstant::EDIT->value,
        VoterSupportConstant::CLONE->value,
        VoterSupportConstant::DELETE->value
    ];

    protected function supports(string $attribute, mixed $subject): bool
    {
        if (!in_array($attribute, self::SUPPORTS, true)) {
            return false;
        }

        if (!$subject instanceof EventSchedule) {
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

        if (!$subject instanceof EventSchedule) {
            return false;
        }

        switch ($attribute) {
            case VoterSupportConstant::VIEW->value:
                return $this->canView($subject, $user);
            case VoterSupportConstant::EDIT->value:
                return $this->canEdit($subject, $user);
            case VoterSupportConstant::CLONE->value:
                return $this->canClone($subject, $user);
            case VoterSupportConstant::DELETE->value:
                return $this->canDelete($subject, $user);
        }

        throw new LogicException('This code should not be reached!');
    }

    private function canView(EventSchedule $eventSchedule, User $user): bool
    {
        return $eventSchedule->isOwner($user);
    }

    private function canEdit(EventSchedule $eventSchedule, User $user): bool
    {
        return $this->canView($eventSchedule, $user);
    }

    private function canClone(EventSchedule $eventSchedule, User $user): bool
    {
        return $this->canView($eventSchedule, $user);
    }

    private function canDelete(EventSchedule $eventSchedule, User $user): bool
    {
        return $this->canView($eventSchedule, $user);
    }
}