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

namespace FinalWork\FinalWorkBundle\Services;

use FinalWork\FinalWorkBundle\Constant\WorkUserTypeConstant;
use FinalWork\FinalWorkBundle\Entity\{
    Work,
    WorkStatus
};
use FinalWork\SonataUserBundle\Entity\User;

class ConversationVariationService
{
    /**
     * @var array|null
     */
    private $newPermission;

    /**
     * @var ParametersService
     */
    private $parametersService;

    /**
     * ConversationVariationService constructor.
     * @param ParametersService $parametersService
     */
    public function __construct(ParametersService $parametersService)
    {
        $this->parametersService = $parametersService;
    }

    /**
     * @param array|null $newPermission
     */
    public function setNewPermission(?array $newPermission): void
    {
        $this->newPermission = $newPermission;
    }

    /**
     * @param User $user
     * @param WorkStatus $workStatus
     * @return array
     */
    public function getWorkConversationsByUser(
        User $user,
        WorkStatus $workStatus
    ): array {
        $works = [];

        $addWorks = function ($userWorks) use (&$works) {
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

    /**
     * @param Work $work
     * @param User $user
     * @return array
     */
    public function getConversationsByWorkUser(
        Work $work,
        User $user
    ): array {
        $conversationUsers = [];
        $addConversationUser = function ($workUsers) use (&$conversationUsers) {
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
            [$a, $o, $s, $c] = $this->getVariationType(WorkUserTypeConstant::OPPONENT, true);

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

    /**
     * @param Work $work
     * @param User $userOne
     * @param User $userTwo
     * @return bool
     */
    public function checker(
        Work $work,
        User $userOne,
        User $userTwo
    ): bool {
        $users = $this->getConversationsByWorkUser($work, $userOne);

        return in_array($userTwo, $users, true);
    }

    /**
     * @param string $type
     * @param bool $onlyValue
     * @return array
     */
    private function getVariationType(
        string $type,
        bool $onlyValue = false
    ): array {
        $permission = $this->getConversationVariationPermissions();
        $variations = $permission[$type];

        if ($onlyValue === true) {
            return array_values($variations);
        }

        return $variations;
    }

    /**
     * @return array
     */
    public function getConversationVariationPermissions(): array
    {
        return $this->newPermission ?? $this->parametersService->getParam('conversation.variation');
    }
}
