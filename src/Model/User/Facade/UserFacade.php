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

namespace App\Model\User\Facade;

use App\Entity\{
    User,
    WorkStatus
};
use App\Helper\UserRoleHelper;
use App\Model\User\Service\UserWorkService;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query;
use App\Constant\WorkUserTypeConstant;
use App\Repository\UserRepository;

class UserFacade
{
    public function __construct(
        private UserRepository $userRepository,
        private UserWorkService $userWorkService
    ) {
    }

    public function find(int $id): ?User
    {
        return $this->userRepository->find($id);
    }

    public function getAllUserActiveSupervisors(User $user): ArrayCollection
    {
        $userActiveSupervisors = new ArrayCollection;

        if (UserRoleHelper::isAuthor($user)) {
            $authorSupervisors = $this->userWorkService->getActiveSupervisor($user, WorkUserTypeConstant::AUTHOR);

            foreach ($authorSupervisors as $supervisor) {
                if (!$userActiveSupervisors->contains($supervisor)) {
                    $userActiveSupervisors->add($supervisor);
                }
            }
        }

        if (UserRoleHelper::isOpponent($user)) {
            $opponentSupervisors = $this->userWorkService->getActiveSupervisor($user, WorkUserTypeConstant::OPPONENT);

            foreach ($opponentSupervisors as $supervisor) {
                if (!$userActiveSupervisors->contains($supervisor)) {
                    $userActiveSupervisors->add($supervisor);
                }
            }
        }

        if (UserRoleHelper::isConsultant($user)) {
            $consultantSupervisors = $this->userWorkService->getActiveSupervisor($user, WorkUserTypeConstant::CONSULTANT);

            foreach ($consultantSupervisors as $supervisor) {
                if (!$userActiveSupervisors->contains($supervisor)) {
                    $userActiveSupervisors->add($supervisor);
                }
            }
        }

        return $userActiveSupervisors;
    }

    public function getUsersQueryBySupervisor(
        User $user,
        string $type,
        iterable|WorkStatus $workStatus = null
    ): Query {
        return $this->userRepository
            ->bySupervisor($user, $type, $workStatus)
            ->getQuery();
    }

    public function queryUnusedUsers(User $user): Query
    {
        return $this->userRepository
            ->unusedUser($user)
            ->getQuery();
    }

    public function findOneByUsername(
        string $username,
        bool $enable = null
    ): ?User {
        return $this->userRepository
            ->byUsername($username, $enable)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findOneByEmail(
        string $email,
        bool $enable = null
    ): ?User {
        return $this->userRepository
            ->byEmail($email, $enable)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
