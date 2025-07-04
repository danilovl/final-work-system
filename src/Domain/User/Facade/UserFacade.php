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

namespace App\Domain\User\Facade;

use App\Domain\User\Entity\User;
use App\Domain\User\Helper\UserRoleHelper;
use App\Domain\User\Repository\UserRepository;
use App\Domain\User\Service\UserWorkService;
use App\Domain\Work\Constant\WorkUserTypeConstant;
use App\Domain\WorkStatus\Entity\WorkStatus;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query;

readonly class UserFacade
{
    public function __construct(
        private UserRepository $userRepository,
        private UserWorkService $userWorkService
    ) {}

    public function findById(int $id): ?User
    {
        /** @var User|null $result */
        $result = $this->userRepository->find($id);

        return $result;
    }

    public function getById(int $id): User
    {
        /** @var User $user */
        $user = $this->userRepository->find($id);

        return $user;
    }

    /**
     * @return ArrayCollection<User>
     */
    public function listUserActiveSupervisors(User $user): ArrayCollection
    {
        /** @var ArrayCollection<User> $userActiveSupervisors */
        $userActiveSupervisors = new ArrayCollection;

        if (UserRoleHelper::isAuthor($user)) {
            $authorSupervisors = $this->userWorkService->getActiveSupervisor($user, WorkUserTypeConstant::AUTHOR->value);

            foreach ($authorSupervisors as $supervisor) {
                if (!$userActiveSupervisors->contains($supervisor)) {
                    $userActiveSupervisors->add($supervisor);
                }
            }
        }

        if (UserRoleHelper::isOpponent($user)) {
            $opponentSupervisors = $this->userWorkService->getActiveSupervisor($user, WorkUserTypeConstant::OPPONENT->value);

            foreach ($opponentSupervisors as $supervisor) {
                if (!$userActiveSupervisors->contains($supervisor)) {
                    $userActiveSupervisors->add($supervisor);
                }
            }
        }

        if (UserRoleHelper::isConsultant($user)) {
            $consultantSupervisors = $this->userWorkService->getActiveSupervisor($user, WorkUserTypeConstant::CONSULTANT->value);

            foreach ($consultantSupervisors as $supervisor) {
                if (!$userActiveSupervisors->contains($supervisor)) {
                    $userActiveSupervisors->add($supervisor);
                }
            }
        }

        return $userActiveSupervisors;
    }

    public function queryBySupervisor(
        User $user,
        string $type,
        iterable|WorkStatus|null $workStatus = null
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

    public function findByUsername(string $username, ?bool $enable = null): ?User
    {
        /** @var User|null $result */
        $result = $this->userRepository
            ->oneByUsername($username, $enable)
            ->getQuery()
            ->getOneOrNullResult();

        return $result;
    }

    public function findByEmail(string $email, ?bool $enable = null): ?User
    {
        /** @var User|null $result */
        $result = $this->userRepository
            ->oneByEmail($email, $enable)
            ->getQuery()
            ->getOneOrNullResult();

        return $result;
    }

    public function findByToken(string $username, string $token, ?bool $enable = null): ?User
    {
        /** @var User|null $result */
        $result = $this->userRepository
            ->oneByToken($username, $token, $enable)
            ->getQuery()
            ->getOneOrNullResult();

        return $result;
    }
}
