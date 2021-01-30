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

use Generator;
use App\Constant\{
    WorkStatusConstant,
    WorkUserTypeConstant
};
use App\Entity\{
    User,
    Work
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

        $userWorks = match ($userType) {
            WorkUserTypeConstant::AUTHOR => $this->arrayGenerator($user->getAuthorWorks()),
            WorkUserTypeConstant::OPPONENT => $this->arrayGenerator($user->getOpponentWorks()),
            WorkUserTypeConstant::CONSULTANT => $this->arrayGenerator($user->getConsultantWorks()),
            default => new ArrayCollection,
        };

        foreach ($userWorks as $work) {
            /** @var Work $work */
            if ($work->getStatus()->getId() === WorkStatusConstant::ACTIVE) {
                /** @var User|null $supervisor */
                $supervisor = $work->getSupervisor();
                if ($supervisor !== null &&
                    $supervisors->contains($supervisor) === false &&
                    $supervisor->isEnabled()
                ) {
                    $supervisors->add($supervisor);
                }
            }
        }

        return $supervisors;
    }

    public function getActiveAuthor(
        User $user,
        string $userType
    ): ArrayCollection {
        $authors = new ArrayCollection;

        $userWorks = match ($userType) {
            WorkUserTypeConstant::OPPONENT => $this->arrayGenerator($user->getOpponentWorks()),
            WorkUserTypeConstant::SUPERVISOR => $this->arrayGenerator($user->getSupervisorWorks()),
            WorkUserTypeConstant::CONSULTANT => $this->arrayGenerator($user->getConsultantWorks()),
            default => new ArrayCollection,
        };

        foreach ($userWorks as $work) {
            /** @var Work $work */
            if ($work->getStatus()->getId() === WorkStatusConstant::ACTIVE) {
                /** @var User|null $supervisor */
                $author = $work->getAuthor();
                if ($author !== null &&
                    $authors->contains($author) === false &&
                    $author->isEnabled()
                ) {
                    $authors->add($author);
                }
            }
        }

        return $authors;
    }

    public function getActiveOpponent(
        User $user,
        string $userType
    ): ArrayCollection {
        $opponents = new ArrayCollection;

        $userWorks = match ($userType) {
            WorkUserTypeConstant::AUTHOR => $this->arrayGenerator($user->getAuthorWorks()),
            WorkUserTypeConstant::SUPERVISOR => $this->arrayGenerator($user->getSupervisorWorks()),
            WorkUserTypeConstant::CONSULTANT => $this->arrayGenerator($user->getConsultantWorks()),
            default => new ArrayCollection,
        };

        foreach ($userWorks as $work) {
            /** @var Work $work */
            if ($work->getStatus()->getId() === WorkStatusConstant::ACTIVE) {
                /** @var User|null $supervisor */
                $opponent = $work->getOpponent();
                if ($opponent !== null &&
                    $opponents->contains($opponent) === false &&
                    $opponent->isEnabled()
                ) {
                    $opponents->add($opponent);
                }
            }
        }

        return $opponents;
    }

    public function getActiveConsultant(
        User $user,
        string $userType
    ): ArrayCollection {
        $consultants = new ArrayCollection;

        $userWorks = match ($userType) {
            WorkUserTypeConstant::CONSULTANT => $this->arrayGenerator($user->getAuthorWorks()),
            WorkUserTypeConstant::SUPERVISOR => $this->arrayGenerator($user->getSupervisorWorks()),
            WorkUserTypeConstant::OPPONENT => $this->arrayGenerator($user->getOpponentWorks()),
            default => new ArrayCollection,
        };

        foreach ($userWorks as $work) {
            /** @var Work $work */
            if ($work->getStatus()->getId() === WorkStatusConstant::ACTIVE) {
                /** @var User|null $consultant */
                $consultant = $work->getConsultant();
                if ($consultant !== null &&
                    $consultants->contains($consultant) === false &&
                    $consultant->isEnabled()
                ) {
                    $consultants->add($consultant);
                }
            }
        }

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

    public function arrayGenerator(Collection $array): Generator
    {
        yield from $array;
    }
}
