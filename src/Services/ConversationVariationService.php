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

namespace App\Services;

use App\Constant\WorkUserTypeConstant;
use Danilovl\ParameterBundle\Services\ParameterService;
use Doctrine\Common\Collections\Collection;
use App\Entity\{
    Work,
    WorkStatus
};
use App\Entity\User;

class ConversationVariationService
{
    private ?array $newPermission = null;
    private ParameterService $parameterService;

    public function __construct(ParameterService $parameterService)
    {
        $this->parameterService = $parameterService;
    }

    public function setNewPermission(?array $newPermission): void
    {
        $this->newPermission = $newPermission;
    }

    public function getWorkConversationsByUser(
        User $user,
        WorkStatus $workStatus
    ): array {
        $works = [];
        $addWorks = function (Collection $userWorks) use (&$works): void {
            foreach ($userWorks as $work) {
                if (!in_array($work, $works, true)) {
                    $works[] = $work;
                }
            }
        };

        if ($user->isAuthor()) {
            $userWorks = $user->getWorkBy(WorkUserTypeConstant::AUTHOR, null, $workStatus);
            $addWorks($userWorks);
        }

        if ($user->isOpponent()) {
            $userWorks = $user->getWorkBy(WorkUserTypeConstant::OPPONENT, null, $workStatus);
            $addWorks($userWorks);
        }

        if ($user->isConsultant()) {
            $userWorks = $user->getWorkBy(WorkUserTypeConstant::CONSULTANT, null, $workStatus);
            $addWorks($userWorks);
        }

        if ($user->isSupervisor()) {
            $userWorks = $user->getWorkBy(WorkUserTypeConstant::SUPERVISOR, null, $workStatus);
            $addWorks($userWorks);
        }

        return $works;
    }

    public function getConversationsByWorkUser(
        Work $work,
        User $user
    ): array {
        $conversationUsers = [];
        $addConversationUser = function (array $workUsers) use (&$conversationUsers): void {
            foreach ($workUsers as $workUser) {
                if (!in_array($workUser, $conversationUsers, true)) {
                    $conversationUsers[] = $workUser;
                }
            }
        };

        if ($work->isAuthor($user)) {
            [$a, $s, $o, $c] = $this->getVariationType(WorkUserTypeConstant::AUTHOR, true);

            $workUsers = $work->getUsers($a, $s, $o, $c);
            $addConversationUser($workUsers);
        }

        if ($work->isOpponent($user)) {
            [$a, $s, $o, $c] = $this->getVariationType(WorkUserTypeConstant::OPPONENT, true);

            $workUsers = $work->getUsers($a, $s, $o, $c);
            $addConversationUser($workUsers);
        }

        if ($work->isSupervisor($user)) {
            [$a, $s, $o, $c] = $this->getVariationType(WorkUserTypeConstant::SUPERVISOR, true);

            $workUsers = $work->getUsers($a, $s, $o, $c);
            $addConversationUser($workUsers);
        }

        if ($work->isConsultant($user)) {
            [$a, $s, $o, $c] = $this->getVariationType(WorkUserTypeConstant::CONSULTANT, true);

            $workUsers = $work->getUsers($a, $s, $o, $c);
            $addConversationUser($workUsers);
        }

        return $conversationUsers;
    }

    public function checker(
        Work $work,
        User $userOne,
        User $userTwo
    ): bool {
        return in_array(
            $userTwo,
            $this->getConversationsByWorkUser($work, $userOne),
            true
        );
    }

    private function getVariationType(
        string $type,
        bool $onlyValue = false
    ): array {
        $variations = $this->getConversationVariationPermissions()[$type] ?? [];

        return $onlyValue === true ? array_values($variations) : $variations;
    }

    public function getConversationVariationPermissions(): array
    {
        return $this->newPermission ?? $this->parameterService->get('conversation.variation');
    }
}
