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

namespace App\Domain\Task\Security\Voter;

use App\Application\Constant\VoterSupportConstant;
use App\Application\Helper\WorkRoleHelper;
use App\Domain\Task\Entity\Task;
use App\Domain\User\Entity\User;
use LogicException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class TaskVoter extends Voter
{
    private const SUPPORTS = [
        VoterSupportConstant::EDIT,
        VoterSupportConstant::DELETE,
        VoterSupportConstant::TASK_NOTIFY_COMPLETE
    ];

    protected function supports(string $attribute, mixed $subject): bool
    {
        if (!in_array($attribute, self::SUPPORTS, true)) {
            return false;
        }

        if (!$subject instanceof Task) {
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

        return WorkRoleHelper::isSupervisor($work, $user) && $task->getOwner() === $user;
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