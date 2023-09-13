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

namespace App\Domain\Event\Security\Voter;

use App\Application\Constant\{
    DateFormatConstant,
    VoterSupportConstant
};
use App\Application\Helper\DateHelper;
use App\Domain\Event\Entity\Event;
use App\Domain\User\Entity\User;
use LogicException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class EventVoter extends Voter
{
    public const SUPPORTS = [
        VoterSupportConstant::VIEW->value,
        VoterSupportConstant::EDIT->value,
        VoterSupportConstant::DELETE->value,
        VoterSupportConstant::RESERVATION->value,
        VoterSupportConstant::SWITCH_TO_SKYPE->value
    ];

    protected function supports(string $attribute, mixed $subject): bool
    {
        if (!in_array($attribute, self::SUPPORTS, true)) {
            return false;
        }

        if (!$subject instanceof Event) {
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
            case VoterSupportConstant::VIEW->value:
                return $this->canView($subject, $user);
            case VoterSupportConstant::SWITCH_TO_SKYPE->value:
                return $this->switchToSkype($subject, $user);
            case VoterSupportConstant::RESERVATION->value:
                return $this->canReservation($subject);
            case VoterSupportConstant::EDIT->value:
                return $this->canEdit($subject, $user);
            case VoterSupportConstant::DELETE->value:
                return $this->canDelete($subject, $user);
        }

        throw new LogicException('This code should not be reached!');
    }

    private function canView(Event $event, User $user): bool
    {
        $owner = false;
        $participant = false;

        if ($event->isOwner($user)) {
            $owner = true;
        }

        if ($event->getParticipant() &&
            $event->getParticipant()->getUser() &&
            $event->getParticipant()->getUser()->getId() === $user->getId()
        ) {
            $participant = true;
        }

        return $owner || $participant;
    }

    private function canReservation(Event $event): bool
    {
        return $event->getParticipant() === null &&
            $event->getType()->isRegistrable() &&
            DateHelper::actualDay() < $event->getStart()->format(DateFormatConstant::DATABASE->value);
    }

    private function switchToSkype(Event $event, User $user): bool
    {
        return $this->canView($event, $user) && $event->getAddress()->isSkype();
    }

    private function canEdit(Event $event, User $user): bool
    {
        return $event->isOwner($user);
    }

    private function canDelete(Event $event, User $user): bool
    {
        return $this->canEdit($event, $user);
    }
}