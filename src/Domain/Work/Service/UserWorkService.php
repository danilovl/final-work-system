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
use App\Domain\Work\Constant\{
    WorkUserTypeConstant
};
use App\Domain\Work\Entity\Work;
use App\Domain\Work\Facade\WorkFacade;
use App\Domain\WorkStatus\Constant\WorkStatusConstant;
use App\Domain\WorkStatus\Entity\WorkStatus;
use App\Domain\WorkType\Entity\WorkType;
use Doctrine\Common\Collections\{
    ArrayCollection,
    Collection
};
use App\Domain\User\Helper\UserRoleHelper;

class UserWorkService
{
    public function __construct(private readonly WorkFacade $workFacade) {}

    /**
     * @return ArrayCollection<User>
     */
    public function getActiveSupervisor(
        User $user,
        string $userType
    ): ArrayCollection {
        $supervisors = new ArrayCollection;
        $userWorks = $this->getUserWorks($user, $userType);

        $this->addUserToCollection($supervisors, $userWorks, WorkUserTypeConstant::SUPERVISOR->value);

        return $supervisors;
    }

    /**
     * @return ArrayCollection<User>
     */
    public function getActiveAuthor(
        User $user,
        string $userType
    ): ArrayCollection {
        $authors = new ArrayCollection;
        $userWorks = $this->getUserWorks($user, $userType);

        $this->addUserToCollection($authors, $userWorks, WorkUserTypeConstant::AUTHOR->value);

        return $authors;
    }

    /**
     * @return ArrayCollection<User>
     */
    public function getActiveOpponent(
        User $user,
        string $userType
    ): ArrayCollection {
        $opponents = new ArrayCollection;
        $userWorks = $this->getUserWorks($user, $userType);

        $this->addUserToCollection($opponents, $userWorks, WorkUserTypeConstant::OPPONENT->value);

        return $opponents;
    }

    /**
     * @return ArrayCollection<User>
     */
    public function getActiveConsultant(
        User $user,
        string $userType
    ): ArrayCollection {
        $consultants = new ArrayCollection;
        $userWorks = $this->getUserWorks($user, $userType);

        $this->addUserToCollection($consultants, $userWorks, WorkUserTypeConstant::CONSULTANT->value);

        return $consultants;
    }

    /**
     * @return ArrayCollection<Work>
     */
    public function getWorkBy(
        User $user,
        string $userType,
        ?WorkType $type = null,
        ?WorkStatus $status = null
    ): ArrayCollection {
        $works = $this->workFacade->listByUserRole(
            user: $user,
            type: $userType,
            workType: $type,
            workStatus: $status
        );

        return new ArrayCollection($works);
    }

    /**
     * @return Collection<Work>
     */
    private function getUserWorks(User $user, string $userType): Collection
    {
        return match ($userType) {
            WorkUserTypeConstant::AUTHOR->value => $user->getAuthorWorks(),
            WorkUserTypeConstant::OPPONENT->value => $user->getOpponentWorks(),
            WorkUserTypeConstant::CONSULTANT->value => $user->getConsultantWorks(),
            WorkUserTypeConstant::SUPERVISOR->value => $user->getSupervisorWorks(),
            default => new ArrayCollection,
        };
    }

    /**
     * @param Collection<Work> $userWorks
     */
    private function addUserToCollection(
        Collection $users,
        Collection $userWorks,
        string $userType
    ): void {
        foreach ($userWorks as $work) {
            if ($work->getStatus()->getId() !== WorkStatusConstant::ACTIVE->value) {
                continue;
            }

            $user = match ($userType) {
                WorkUserTypeConstant::AUTHOR->value => $work->getAuthor(),
                WorkUserTypeConstant::OPPONENT->value => $work->getOpponent(),
                WorkUserTypeConstant::CONSULTANT->value => $work->getConsultant(),
                WorkUserTypeConstant::SUPERVISOR->value => $work->getSupervisor(),
                default => throw new RuntimeException("UserType '{$userType}' not found")
            };

            if ($user === null || !$user->isEnabled() || $users->contains($user)) {
                continue;
            }

            $users->add($user);
        }
    }

    /**
     * @return ArrayCollection<User>
     */
    public function listUserActiveSupervisors(User $user): ArrayCollection
    {
        /** @var ArrayCollection<User> $userActiveSupervisors */
        $userActiveSupervisors = new ArrayCollection;

        if (UserRoleHelper::isAuthor($user)) {
            $authorSupervisors = $this->getActiveSupervisor($user, WorkUserTypeConstant::AUTHOR->value);

            foreach ($authorSupervisors as $supervisor) {
                if (!$userActiveSupervisors->contains($supervisor)) {
                    $userActiveSupervisors->add($supervisor);
                }
            }
        }

        if (UserRoleHelper::isOpponent($user)) {
            $opponentSupervisors = $this->getActiveSupervisor($user, WorkUserTypeConstant::OPPONENT->value);

            foreach ($opponentSupervisors as $supervisor) {
                if (!$userActiveSupervisors->contains($supervisor)) {
                    $userActiveSupervisors->add($supervisor);
                }
            }
        }

        if (UserRoleHelper::isConsultant($user)) {
            $consultantSupervisors = $this->getActiveSupervisor($user, WorkUserTypeConstant::CONSULTANT->value);

            foreach ($consultantSupervisors as $supervisor) {
                if (!$userActiveSupervisors->contains($supervisor)) {
                    $userActiveSupervisors->add($supervisor);
                }
            }
        }

        return $userActiveSupervisors;
    }
}
