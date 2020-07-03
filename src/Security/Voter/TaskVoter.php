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
    Task
};
use LogicException;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class TaskVoter extends Voter
{
    private const SUPPORTS = [
        VoterSupportConstant::EDIT,
        VoterSupportConstant::DELETE,
        VoterSupportConstant::TASK_NOTIFY_COMPLETE
    ];

    protected function supports($attribute, $subject): bool
    {
        if (!in_array($attribute, self::SUPPORTS, true)) {
            return false;
        }

        if (!$subject instanceof Task) {
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
            case VoterSupportConstant::EDIT:
                return $this->canEdit($subject, $user);
            case VoterSupportConstant::DELETE:
                return $this->canDelete($subject, $user);
            case VoterSupportConstant::TASK_NOTIFY_COMPLETE:
                return $this->canNotifyComplete($subject, $user);
        }

        throw new LogicException('This code should not be reached!');
    }

    private function canEdit(Task $task, User $user): bool
    {
        $work = $task->getWork();

        return $work->isSupervisor($user) && $task->getOwner() === $user;
    }

    private function canDelete(Task $task, User $user): bool
    {
        return $this->canEdit($task, $user);
    }

    private function canNotifyComplete(Task $task, User $user): bool
    {
        $work = $task->getWork();

        return $work->getAuthor()->getId() === $user->getId();
    }
}