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

namespace App\Service;

use App\Entity\{
    User,
    Work
};
use App\Constant\{
    WorkStatusConstant,
    WorkUserTypeConstant
};
use App\Exception\RuntimeException;
use Doctrine\Common\Collections\{
    Criteria,
    Collection,
    ArrayCollection
};

class UserWorkService
{
    public function getActiveSupervisor(
        User $user,
        string $userType
    ): ArrayCollection {
        $supervisors = new ArrayCollection;
        $userWorks = $this->getUserWorks($user, $userType);

        $this->addUserToCollection($supervisors, $userWorks, WorkUserTypeConstant::SUPERVISOR);

        return $supervisors;
    }

    public function getActiveAuthor(
        User $user,
        string $userType
    ): ArrayCollection {
        $authors = new ArrayCollection;
        $userWorks = $this->getUserWorks($user, $userType);

        $this->addUserToCollection($authors, $userWorks, WorkUserTypeConstant::AUTHOR);

        return $authors;
    }

    public function getActiveOpponent(
        User $user,
        string $userType
    ): ArrayCollection {
        $opponents = new ArrayCollection;
        $userWorks = $this->getUserWorks($user, $userType);

        $this->addUserToCollection($opponents, $userWorks, WorkUserTypeConstant::OPPONENT);

        return $opponents;
    }

    public function getActiveConsultant(
        User $user,
        string $userType
    ): ArrayCollection {
        $consultants = new ArrayCollection;
        $userWorks = $this->getUserWorks($user, $userType);

        $this->addUserToCollection($consultants, $userWorks, WorkUserTypeConstant::CONSULTANT);

        return $consultants;
    }

    public function getWorkBy(
        User $user,
        string $userType,
        $type = null,
        $status = null
    ): ArrayCollection {
        $collectionWorks = new ArrayCollection;
        $criteria = Criteria::create();

        $userWorks = match ($userType) {
            WorkUserTypeConstant::AUTHOR => $user->getAuthorWorks(),
            WorkUserTypeConstant::SUPERVISOR => $user->getSupervisorWorks(),
            WorkUserTypeConstant::OPPONENT => $user->getOpponentWorks(),
            WorkUserTypeConstant::CONSULTANT => $user->getConsultantWorks(),
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

        return $userWorks;
    }

    /**
     * @return Collection|Work[]
     */
    private function getUserWorks(User $user, string $userType): Collection
    {
        return match ($userType) {
            WorkUserTypeConstant::AUTHOR => $user->getAuthorWorks(),
            WorkUserTypeConstant::OPPONENT => $user->getOpponentWorks(),
            WorkUserTypeConstant::CONSULTANT => $user->getConsultantWorks(),
            WorkUserTypeConstant::SUPERVISOR => $user->getSupervisorWorks(),
            default => new ArrayCollection,
        };
    }

    /**
     * @param Collection|Work[] $userWorks
     */
    private function addUserToCollection(
        Collection $collection,
        Collection $userWorks,
        string $userType
    ): void {
        foreach ($userWorks as $work) {
            if ($work->getStatus()->getId() !== WorkStatusConstant::ACTIVE) {
                continue;
            }

            $user = match ($userType) {
                WorkUserTypeConstant::AUTHOR => $work->getAuthor(),
                WorkUserTypeConstant::OPPONENT => $work->getOpponent(),
                WorkUserTypeConstant::CONSULTANT => $work->getConsultant(),
                WorkUserTypeConstant::SUPERVISOR => $work->getSupervisor(),
                default => throw new RuntimeException("UserType '{$userType}' not found")
            };

            if ($user !== null &&
                $collection->contains($user) === false &&
                $user->isEnabled()
            ) {
                $collection->add($user);
            }
        }
    }
}
