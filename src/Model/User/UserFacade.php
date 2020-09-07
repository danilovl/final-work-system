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

namespace App\Model\User;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query;
use App\Constant\WorkUserTypeConstant;
use App\Entity\User;
use App\Repository\UserRepository;

class UserFacade
{
    private UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function find(int $id): ?User
    {
        return $this->userRepository->find($id);
    }

    public function findByEmail(string $email): ?User
    {
        return $this->userRepository->findOneBy([
            'email' => $email
        ]);
    }

    public function getAllUserActiveSupervisors(User $user): ArrayCollection
    {
        $userActiveSupervisors = new ArrayCollection;

        if ($user->isAuthor()) {
            $authorSupervisors = $user->getActiveSupervisor(WorkUserTypeConstant::AUTHOR);

            foreach ($authorSupervisors as $supervisor) {
                if (!$userActiveSupervisors->contains($supervisor)) {
                    $userActiveSupervisors->add($supervisor);
                }
            }
        }

        if ($user->isOpponent()) {
            $opponentSupervisors = $user->getActiveSupervisor(WorkUserTypeConstant::OPPONENT);

            foreach ($opponentSupervisors as $supervisor) {
                if (!$userActiveSupervisors->contains($supervisor)) {
                    $userActiveSupervisors->add($supervisor);
                }
            }
        }

        if ($user->isConsultant()) {
            $consultantSupervisors = $user->getActiveSupervisor(WorkUserTypeConstant::CONSULTANT);

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
        $workStatus = null
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

    public function findUserByUsername(
        string $username,
        bool $enable = null
    ): ?User {
        return $this->userRepository
            ->byUsername($username, $enable)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findUserByEmail(
        string $email,
        bool $enable = null
    ): ?User {
        return $this->userRepository
            ->byEmail($email, $enable)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
