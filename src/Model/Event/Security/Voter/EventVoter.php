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

namespace App\Model\Event\Security\Voter;

use App\Model\Event\Entity\Event;
use App\Model\User\Entity\User;
use App\Constant\{
    DateFormatConstant,
    VoterSupportConstant
};
use App\Helper\DateHelper;
use LogicException;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class EventVoter extends Voter
{
    private const SUPPORTS = [
        VoterSupportConstant::VIEW,
        VoterSupportConstant::EDIT,
        VoterSupportConstant::DELETE,
        VoterSupportConstant::RESERVATION,
        VoterSupportConstant::SWITCH_TO_SKYPE
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
            case VoterSupportConstant::VIEW:
                return $this->canView($subject, $user);
            case VoterSupportConstant::SWITCH_TO_SKYPE:
                return $this->switchToSkype($subject, $user);
            case VoterSupportConstant::RESERVATION:
                return $this->canReservation($subject);
            case VoterSupportConstant::EDIT:
                return $this->canEdit($subject, $user);
            case VoterSupportConstant::DELETE:
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
            DateHelper::actualDay() < $event->getStart()->format(DateFormatConstant::DATABASE);
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