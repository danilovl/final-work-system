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

namespace App\Service\Work;

use App\Exception\RuntimeException;
use App\Service\User\UserWorkService;
use Doctrine\Common\Collections\{
    Collection,
    ArrayCollection
};
use App\Constant\WorkUserTypeConstant;
use App\Entity\User;

class WorkListService
{
    public function __construct(private UserWorkService $userWorkService)
    {
    }

    public function getWorkList(User $user, string $type): Collection
    {
        return match ($type) {
            WorkUserTypeConstant::SUPERVISOR => $user->getSupervisorWorks(),
            WorkUserTypeConstant::AUTHOR => $user->getAuthorWorks(),
            WorkUserTypeConstant::OPPONENT => $user->getOpponentWorks(),
            WorkUserTypeConstant::CONSULTANT => $user->getConsultantWorks(),
            default => throw new RuntimeException("Type '{$type}' not found")
        };
    }

    public function getUserAuthors(User $user, string $type): Collection
    {
        return match ($type) {
            WorkUserTypeConstant::SUPERVISOR => $this->userWorkService->getActiveAuthor($user, WorkUserTypeConstant::SUPERVISOR),
            WorkUserTypeConstant::OPPONENT => $this->userWorkService->getActiveAuthor($user, WorkUserTypeConstant::OPPONENT),
            WorkUserTypeConstant::CONSULTANT => $this->userWorkService->getActiveAuthor($user, WorkUserTypeConstant::CONSULTANT),
            default => new ArrayCollection,
        };
    }

    public function getUserOpponents(User $user, string $type): Collection
    {
        return match ($type) {
            WorkUserTypeConstant::SUPERVISOR => $this->userWorkService->getActiveOpponent($user, WorkUserTypeConstant::SUPERVISOR),
            WorkUserTypeConstant::AUTHOR => $this->userWorkService->getActiveOpponent($user, WorkUserTypeConstant::AUTHOR),
            default => new ArrayCollection,
        };
    }

    public function getUserConsultants(User $user, string $type): Collection
    {
        return match ($type) {
            WorkUserTypeConstant::SUPERVISOR => $this->userWorkService->getActiveConsultant($user, WorkUserTypeConstant::SUPERVISOR),
            WorkUserTypeConstant::AUTHOR => $this->userWorkService->getActiveConsultant($user, WorkUserTypeConstant::AUTHOR),
            WorkUserTypeConstant::OPPONENT => $this->userWorkService->getActiveConsultant($user, WorkUserTypeConstant::OPPONENT),
            WorkUserTypeConstant::CONSULTANT => $this->userWorkService->getActiveConsultant($user, WorkUserTypeConstant::CONSULTANT),
            default => throw new RuntimeException("Type '{$type}' not found")
        };
    }

    public function getUserSupervisors(User $user, string $type): Collection
    {
        return match ($type) {
            WorkUserTypeConstant::AUTHOR => $this->userWorkService->getActiveSupervisor($user, WorkUserTypeConstant::AUTHOR),
            WorkUserTypeConstant::OPPONENT => $this->userWorkService->getActiveSupervisor($user, WorkUserTypeConstant::OPPONENT),
            WorkUserTypeConstant::CONSULTANT => $this->userWorkService->getActiveSupervisor($user, WorkUserTypeConstant::CONSULTANT),
            default => new ArrayCollection,
        };
    }
}

