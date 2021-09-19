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

namespace App\Model\Work\Service;

use App\Exception\RuntimeException;
use App\Model\User\Service\UserWorkService;
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
        $userTypes = [
            WorkUserTypeConstant::SUPERVISOR,
            WorkUserTypeConstant::OPPONENT,
            WorkUserTypeConstant::CONSULTANT
        ];

        if (!in_array($type, $userTypes, true)) {
            return new ArrayCollection;
        }

        return $this->userWorkService->getActiveAuthor($user, $type);
    }

    public function getUserOpponents(User $user, string $type): Collection
    {
        $userTypes = [
            WorkUserTypeConstant::SUPERVISOR,
            WorkUserTypeConstant::AUTHOR
        ];

        if (!in_array($type, $userTypes, true)) {
            return new ArrayCollection;
        }

        return $this->userWorkService->getActiveOpponent($user, $type);
    }

    public function getUserConsultants(User $user, string $type): Collection
    {
        $userTypes = [
            WorkUserTypeConstant::SUPERVISOR,
            WorkUserTypeConstant::AUTHOR,
            WorkUserTypeConstant::OPPONENT,
            WorkUserTypeConstant::CONSULTANT
        ];

        if (!in_array($type, $userTypes, true)) {
            return new ArrayCollection;
        }

        return $this->userWorkService->getActiveConsultant($user, $type);
    }

    public function getUserSupervisors(User $user, string $type): Collection
    {
        $userTypes = [
            WorkUserTypeConstant::AUTHOR,
            WorkUserTypeConstant::OPPONENT,
            WorkUserTypeConstant::CONSULTANT
        ];

        if (!in_array($type, $userTypes, true)) {
            return new ArrayCollection;
        }

        return $this->userWorkService->getActiveSupervisor($user, $type);
    }
}