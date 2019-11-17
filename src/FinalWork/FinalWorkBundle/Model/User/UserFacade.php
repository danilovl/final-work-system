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

namespace FinalWork\FinalWorkBundle\Model\User;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\{
    Query,
    EntityManager
};
use FinalWork\FinalWorkBundle\Constant\WorkUserTypeConstant;
use FinalWork\FinalWorkBundle\Entity\WorkStatus;
use FinalWork\SonataUserBundle\Entity\User;
use FinalWork\SonataUserBundle\Entity\Repository\UserRepository;

class UserFacade
{
    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * UserFacade constructor.
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->userRepository = $entityManager->getRepository(User::class);
    }

    /**
     * @param int $id
     * @return User|null
     */
    public function find(int $id): ?User
    {
        return $this->userRepository->find($id);
    }

    /**
     * @param User $user
     * @return ArrayCollection
     */
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

    /**
     * @param User $user
     * @param string $type
     * @param WorkStatus|iterable|null $workStatus
     * @return Query
     */
    public function getUsersQueryBySupervisor(
        User $user,
        string $type,
        $workStatus = null
    ): Query {
        return $this->userRepository
            ->findUserBySupervisor($user, $type, $workStatus)
            ->getQuery();
    }

    /**
     * @param User $user
     * @return Query
     */
    public function queryUnusedUsers(User $user): Query
    {
        return $this->userRepository
            ->findUnusedUser($user)
            ->getQuery();
    }
}
