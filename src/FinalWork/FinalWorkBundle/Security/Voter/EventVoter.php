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

namespace FinalWork\FinalWorkBundle\Security\Voter;

use FinalWork\FinalWorkBundle\Constant\VoterSupportConstant;
use FinalWork\FinalWorkBundle\Entity\Event;
use FinalWork\FinalWorkBundle\Helper\DateHelper;
use FinalWork\SonataUserBundle\Entity\User;
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

        if (!$subject instanceof Event) {
            return false;
        }

        return true;
    }

    /**
     * @param string $attribute
     * @param Event $subject
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

    /**
     * @param Event $event
     * @param User $user
     * @return bool
     */
    private function canView(Event $event, User $user): bool
    {
        $owner = false;
        $participant = false;

        if ($event->isOwner($user)) {
            $owner = true;
        }

        if ($event->getParticipant() &&
            $event->getParticipant()->getUser() &&
            $event->getParticipant()->getUser()->getId() === $user->getId()) {
            $participant = true;
        }

        return $owner || $participant;
    }

    /**
     * @param Event $event
     * @return bool
     */
    private function canReservation(Event $event): bool
    {
        return $event->getParticipant() === null &&
            $event->getType()->isRegistrable() &&
            DateHelper::actualDay() < $event->getStart()->format('Y-m-d H:i:s');
    }

    /**
     * @param Event $event
     * @param User $user
     * @return bool
     */
    private function switchToSkype(Event $event, User $user): bool
    {
        return $this->canView($event, $user) && $event->getAddress()->isSkype();
    }

    /**
     * @param Event $event
     * @param User $user
     * @return bool
     */
    private function canEdit(Event $event, User $user): bool
    {
        return $event->isOwner($user);
    }

    /**
     * @param Event $event
     * @param User $user
     * @return bool
     */
    private function canDelete(Event $event, User $user): bool
    {
        return $this->canEdit($event, $user);
    }
}