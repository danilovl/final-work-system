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

use App\Exception\RuntimeException;
use ArrayIterator;
use Collator;
use Doctrine\Common\Collections\{
    Criteria,
    Collection,
    ArrayCollection
};
use App\Constant\{
    WorkStatusConstant,
    WorkUserTypeConstant
};
use App\Entity\{
    Work,
    WorkStatus
};
use App\Entity\User;
use Symfony\Component\Form\FormInterface;

class WorkListService
{
    public function __construct(
        private EntityManagerService $entityManagerService,
        private UserWorkService $userWorkService
    ) {
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
        return match ($type) {
            WorkUserTypeConstant::SUPERVISOR => $this->userWorkService->getActiveAuthor($user, WorkUserTypeConstant::SUPERVISOR),
            WorkUserTypeConstant::OPPONENT => $this->userWorkService->getActiveAuthor($user, WorkUserTypeConstant::OPPONENT),
            WorkUserTypeConstant::CONSULTANT => $this->userWorkService->getActiveAuthor($user, WorkUserTypeConstant::CONSULTANT),
            default => new ArrayCollection,
        };
    }

    public function getUserOpponents(User $user, string $type): Collection
    {
        return match ($type) {
            WorkUserTypeConstant::SUPERVISOR => $this->userWorkService->getActiveOpponent($user, WorkUserTypeConstant::SUPERVISOR),
            WorkUserTypeConstant::AUTHOR => $this->userWorkService->getActiveOpponent($user, WorkUserTypeConstant::AUTHOR),
            default => new ArrayCollection,
        };
    }

    public function getUserConsultants(User $user, string $type): Collection
    {
        return match ($type) {
            WorkUserTypeConstant::SUPERVISOR => $this->userWorkService->getActiveConsultant($user, WorkUserTypeConstant::SUPERVISOR),
            WorkUserTypeConstant::AUTHOR => $this->userWorkService->getActiveConsultant($user, WorkUserTypeConstant::AUTHOR),
            WorkUserTypeConstant::OPPONENT => $this->userWorkService->getActiveConsultant($user, WorkUserTypeConstant::OPPONENT),
            WorkUserTypeConstant::CONSULTANT => $this->userWorkService->getActiveConsultant($user, WorkUserTypeConstant::CONSULTANT),
            default => throw new RuntimeException("Type '{$type}' not found")
        };
    }

    public function getUserSupervisors(User $user, string $type): Collection
    {
        return match ($type) {
            WorkUserTypeConstant::AUTHOR => $this->userWorkService->getActiveSupervisor($user, WorkUserTypeConstant::AUTHOR),
            WorkUserTypeConstant::OPPONENT => $this->userWorkService->getActiveSupervisor($user, WorkUserTypeConstant::OPPONENT),
            WorkUserTypeConstant::CONSULTANT => $this->userWorkService->getActiveSupervisor($user, WorkUserTypeConstant::CONSULTANT),
            default => new ArrayCollection,
        };
    }

    public function filter(FormInterface $form, Collection $works): ArrayIterator
    {
        if ($form->isSubmitted() && $form->isValid()) {
            $criteria = Criteria::create();

            foreach ($form->getData() as $field => $value) {
                if (!empty($value)) {

                    if (is_iterable($value) && count($value) > 0) {
                        foreach ($value as $item) {
                            if ($field === 'deadline') {
                                $criteria->orWhere(
                                    Criteria::expr()->andX(
                                        Criteria::expr()->gte('deadline', $item),
                                        Criteria::expr()->lte('deadline', $item)
                                    )
                                );
                            } else {
                                $criteria->orWhere(Criteria::expr()->eq($field, $item));
                            }
                        }
                    } elseif (is_string($value)) {
                        $criteria->orWhere(Criteria::expr()->contains($field, $value));
                    } else {
                        $criteria->orWhere(Criteria::expr()->eq($field, $value));
                    }
                }
            }
            $works = $works->matching($criteria);

        } else {
            $criteria = Criteria::create()
                ->where(
                    Criteria::expr()->eq(
                        'status',
                        $this->entityManagerService->getReference(WorkStatus::class, WorkStatusConstant::ACTIVE))
                );
            $works = $works->matching($criteria);
        }

        $collator = new Collator('cs_CZ.UTF-8');
        $iterator = $works->getIterator();
        $iterator->uasort(static function (Work $first, Work $second) use ($collator): bool|int {
            $f = $first->getAuthor()->getLastname();
            $s = $second->getAuthor()->getLastname();

            return $collator->compare($f, $s);
        });

        return $iterator;
    }
}
