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

namespace App\Services;

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
    public function __construct(private EntityManagerService $em)
    {
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
            WorkUserTypeConstant::SUPERVISOR => $user->getActiveAuthor(WorkUserTypeConstant::SUPERVISOR),
            WorkUserTypeConstant::OPPONENT => $user->getActiveAuthor(WorkUserTypeConstant::OPPONENT),
            WorkUserTypeConstant::CONSULTANT => $user->getActiveAuthor(WorkUserTypeConstant::CONSULTANT),
            default => new ArrayCollection,
        };
    }

    public function getUserOpponents(User $user, string $type): Collection
    {
        return match ($type) {
            WorkUserTypeConstant::SUPERVISOR => $user->getActiveOpponent(WorkUserTypeConstant::SUPERVISOR),
            WorkUserTypeConstant::AUTHOR => $user->getActiveOpponent(WorkUserTypeConstant::AUTHOR),
            default => new ArrayCollection,
        };
    }

    public function getUserConsultants(User $user, string $type): Collection
    {
        return match ($type) {
            WorkUserTypeConstant::SUPERVISOR => $user->getActiveConsultant(WorkUserTypeConstant::SUPERVISOR),
            WorkUserTypeConstant::AUTHOR => $user->getActiveConsultant(WorkUserTypeConstant::AUTHOR),
            WorkUserTypeConstant::OPPONENT => $user->getActiveConsultant(WorkUserTypeConstant::OPPONENT),
            WorkUserTypeConstant::CONSULTANT => $user->getActiveOpponent(WorkUserTypeConstant::CONSULTANT),
            default => throw new RuntimeException("Type '{$type}' not found")
        };
    }

    public function getUserSupervisors(User $user, string $type): Collection
    {
        return match ($type) {
            WorkUserTypeConstant::AUTHOR => $user->getActiveSupervisor(WorkUserTypeConstant::AUTHOR),
            WorkUserTypeConstant::OPPONENT => $user->getActiveSupervisor(WorkUserTypeConstant::OPPONENT),
            WorkUserTypeConstant::CONSULTANT => $user->getActiveSupervisor(WorkUserTypeConstant::CONSULTANT),
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
                        $this->em->getReference(WorkStatus::class, WorkStatusConstant::ACTIVE))
                );
            $works = $works->matching($criteria);
        }

        $collator = new Collator('cs_CZ.UTF-8');
        $iterator = $works->getIterator();
        $iterator->uasort(function ($first, $second) use ($collator) {
            /** @var Work $first */
            /** @var Work $second */
            $f = $first->getAuthor()->getLastname();
            $s = $second->getAuthor()->getLastname();

            return $collator->compare($f, $s);
        });

        return $iterator;
    }
}
