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

namespace App\Domain\Conversation\Service;

use App\Domain\User\Entity\User;
use App\Domain\User\Helper\UserRoleHelper;
use App\Domain\User\Service\UserWorkService;
use App\Domain\Work\Constant\WorkUserTypeConstant;
use App\Domain\Work\Entity\Work;
use App\Domain\Work\Helper\WorkRoleHelper;
use App\Domain\Work\Service\WorkService;
use App\Domain\WorkStatus\Entity\WorkStatus;
use Danilovl\ParameterBundle\Interfaces\ParameterServiceInterface;
use Doctrine\Common\Collections\Collection;
use Webmozart\Assert\Assert;

class ConversationVariationService
{
    private ?array $newPermission = null;

    public function __construct(
        private readonly UserWorkService $userWorkService,
        private readonly WorkService $workService,
        private readonly ParameterServiceInterface $parameterService
    ) {}

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

        if (UserRoleHelper::isAuthor($user)) {
            $userWorks = $this->userWorkService->getWorkBy($user, WorkUserTypeConstant::AUTHOR->value, null, $workStatus);
            $addWorks($userWorks);
        }

        if (UserRoleHelper::isOpponent($user)) {
            $userWorks = $this->userWorkService->getWorkBy($user, WorkUserTypeConstant::OPPONENT->value, null, $workStatus);
            $addWorks($userWorks);
        }

        if (UserRoleHelper::isConsultant($user)) {
            $userWorks = $this->userWorkService->getWorkBy($user, WorkUserTypeConstant::CONSULTANT->value, null, $workStatus);
            $addWorks($userWorks);
        }

        if (UserRoleHelper::isSupervisor($user)) {
            $userWorks = $this->userWorkService->getWorkBy($user, WorkUserTypeConstant::SUPERVISOR->value, null, $workStatus);
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
            Assert::allIsInstanceOf($workUsers, User::class);

            foreach ($workUsers as $workUser) {
                if (!in_array($workUser, $conversationUsers, true)) {
                    $conversationUsers[] = $workUser;
                }
            }
        };

        if (WorkRoleHelper::isAuthor($work, $user)) {
            [$a, $s, $o, $c] = $this->getVariationType(WorkUserTypeConstant::AUTHOR->value, true);

            $workUsers = $this->workService->getUsers($work, $a, $s, $o, $c);
            $addConversationUser($workUsers);
        }

        if (WorkRoleHelper::isOpponent($work, $user)) {
            [$a, $s, $o, $c] = $this->getVariationType(WorkUserTypeConstant::OPPONENT->value, true);

            $workUsers = $this->workService->getUsers($work, $a, $s, $o, $c);
            $addConversationUser($workUsers);
        }

        if (WorkRoleHelper::isSupervisor($work, $user)) {
            [$a, $s, $o, $c] = $this->getVariationType(WorkUserTypeConstant::SUPERVISOR->value, true);

            $workUsers = $this->workService->getUsers($work, $a, $s, $o, $c);
            $addConversationUser($workUsers);
        }

        if (WorkRoleHelper::isConsultant($work, $user)) {
            [$a, $s, $o, $c] = $this->getVariationType(WorkUserTypeConstant::CONSULTANT->value, true);

            $workUsers = $this->workService->getUsers($work, $a, $s, $o, $c);
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
        return $this->newPermission ?? $this->parameterService->getArray('conversation.variation');
    }
}
