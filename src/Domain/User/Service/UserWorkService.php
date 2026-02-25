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

namespace App\Domain\User\Service;

use App\Application\Exception\RuntimeException;
use App\Domain\User\Entity\User;
use App\Domain\Work\Constant\{
    WorkUserTypeConstant
};
use App\Domain\Work\Entity\Work;
use App\Domain\WorkStatus\Constant\WorkStatusConstant;
use App\Domain\WorkStatus\Entity\WorkStatus;
use App\Domain\WorkType\Entity\WorkType;
use Doctrine\Common\Collections\{
    ArrayCollection,
    Collection,
    Criteria
};

class UserWorkService
{
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
        /** @var ArrayCollection<Work> $collectionWorks */
        $collectionWorks = new ArrayCollection;
        $criteria = Criteria::create();

        $userWorks = match ($userType) {
            WorkUserTypeConstant::AUTHOR->value => $user->getAuthorWorks(),
            WorkUserTypeConstant::SUPERVISOR->value => $user->getSupervisorWorks(),
            WorkUserTypeConstant::OPPONENT->value => $user->getOpponentWorks(),
            WorkUserTypeConstant::CONSULTANT->value => $user->getConsultantWorks(),
            default => throw new RuntimeException("UserType '{$userType}' not found")
        };

        if ($type !== null) {
            $criteria->where(Criteria::expr()->eq('type', $type));
            $userWorks = $userWorks->matching($criteria);
        }

        if ($status !== null) {
            $criteria->andWhere(Criteria::expr()->eq('status', $status));
            $userWorks = $userWorks->matching($criteria);
        }

        if (!$userWorks->isEmpty()) {
            foreach ($userWorks as $work) {
                $collectionWorks->add($work);
            }
        }

        return $collectionWorks;
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
}
