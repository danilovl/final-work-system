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

namespace App\Domain\Work\Service;

use App\Application\Exception\RuntimeException;
use App\Domain\User\Entity\User;
use App\Domain\User\Service\UserWorkService;
use App\Domain\Work\Constant\WorkUserTypeConstant;
use Doctrine\Common\Collections\{
    Collection,
    ArrayCollection
};

readonly class WorkListService
{
    public function __construct(private UserWorkService $userWorkService) {}

    public function getWorkList(User $user, string $type): Collection
    {
        return match ($type) {
            WorkUserTypeConstant::SUPERVISOR->value => $user->getSupervisorWorks(),
            WorkUserTypeConstant::AUTHOR->value => $user->getAuthorWorks(),
            WorkUserTypeConstant::OPPONENT->value => $user->getOpponentWorks(),
            WorkUserTypeConstant::CONSULTANT->value => $user->getConsultantWorks(),
            default => throw new RuntimeException("Type '{$type}' not found")
        };
    }

    public function getUserAuthors(User $user, string $type): Collection
    {
        $userTypes = [
            WorkUserTypeConstant::SUPERVISOR->value,
            WorkUserTypeConstant::OPPONENT->value,
            WorkUserTypeConstant::CONSULTANT->value
        ];

        if (!in_array($type, $userTypes, true)) {
            return new ArrayCollection;
        }

        return $this->userWorkService->getActiveAuthor($user, $type);
    }

    public function getUserOpponents(User $user, string $type): Collection
    {
        $userTypes = [
            WorkUserTypeConstant::SUPERVISOR->value,
            WorkUserTypeConstant::AUTHOR->value
        ];

        if (!in_array($type, $userTypes, true)) {
            return new ArrayCollection;
        }

        return $this->userWorkService->getActiveOpponent($user, $type);
    }

    public function getUserConsultants(User $user, string $type): Collection
    {
        $userTypes = [
            WorkUserTypeConstant::SUPERVISOR->value,
            WorkUserTypeConstant::AUTHOR->value,
            WorkUserTypeConstant::OPPONENT->value,
            WorkUserTypeConstant::CONSULTANT->value
        ];

        if (!in_array($type, $userTypes, true)) {
            return new ArrayCollection;
        }

        return $this->userWorkService->getActiveConsultant($user, $type);
    }

    public function getUserSupervisors(User $user, string $type): Collection
    {
        $userTypes = [
            WorkUserTypeConstant::AUTHOR->value,
            WorkUserTypeConstant::OPPONENT->value,
            WorkUserTypeConstant::CONSULTANT->value
        ];

        if (!in_array($type, $userTypes, true)) {
            return new ArrayCollection;
        }

        return $this->userWorkService->getActiveSupervisor($user, $type);
    }
}
