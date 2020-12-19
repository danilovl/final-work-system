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
        $works = new ArrayCollection;

        switch ($type) {
            case WorkUserTypeConstant::SUPERVISOR:
                $works = $user->getSupervisorWorks();
                break;
            case WorkUserTypeConstant::AUTHOR:
                $works = $user->getAuthorWorks();
                break;
            case WorkUserTypeConstant::OPPONENT:
                $works = $user->getOpponentWorks();
                break;
            case WorkUserTypeConstant::CONSULTANT:
                $works = $user->getConsultantWorks();
                break;
        }

        return $works;
    }

    public function getUserAuthors(User $user, string $type): Collection
    {
        $userAuthorArray = new ArrayCollection;

        switch ($type) {
            case WorkUserTypeConstant::SUPERVISOR:
                $userAuthorArray = $user->getActiveAuthor(WorkUserTypeConstant::SUPERVISOR);
                break;
            case WorkUserTypeConstant::OPPONENT:
                $userAuthorArray = $user->getActiveAuthor(WorkUserTypeConstant::OPPONENT);
                break;
            case WorkUserTypeConstant::CONSULTANT:
                $userAuthorArray = $user->getActiveAuthor(WorkUserTypeConstant::CONSULTANT);
                break;
        }

        return $userAuthorArray;
    }

    public function getUserOpponents(User $user, string $type): Collection
    {
        $userOpponentArray = new ArrayCollection;

        switch ($type) {
            case WorkUserTypeConstant::SUPERVISOR:
                $userOpponentArray = $user->getActiveOpponent(WorkUserTypeConstant::SUPERVISOR);
                break;
            case WorkUserTypeConstant::AUTHOR:
                $userOpponentArray = $user->getActiveOpponent(WorkUserTypeConstant::AUTHOR);
                break;
            case WorkUserTypeConstant::CONSULTANT:
                break;
        }

        return $userOpponentArray;
    }

    public function getUserConsultants(User $user, string $type): Collection
    {
        $userConsultantArray = new ArrayCollection;

        switch ($type) {
            case WorkUserTypeConstant::SUPERVISOR:
                $userConsultantArray = $user->getActiveConsultant(WorkUserTypeConstant::SUPERVISOR);
                break;
            case WorkUserTypeConstant::AUTHOR:
                $userConsultantArray = $user->getActiveConsultant(WorkUserTypeConstant::AUTHOR);
                break;
            case WorkUserTypeConstant::OPPONENT:
                $userConsultantArray = $user->getActiveConsultant(WorkUserTypeConstant::OPPONENT);
                break;
            case WorkUserTypeConstant::CONSULTANT:
                $userConsultantArray = $user->getActiveOpponent(WorkUserTypeConstant::CONSULTANT);
                break;
        }

        return $userConsultantArray;
    }

    public function getUserSupervisors(User $user, string $type): Collection
    {
        $userSupervisorArray = new ArrayCollection;

        switch ($type) {
            case WorkUserTypeConstant::AUTHOR:
                $userSupervisorArray = $user->getActiveSupervisor(WorkUserTypeConstant::AUTHOR);
                break;
            case WorkUserTypeConstant::OPPONENT:
                $userSupervisorArray = $user->getActiveSupervisor(WorkUserTypeConstant::OPPONENT);
                break;
            case WorkUserTypeConstant::CONSULTANT:
                $userSupervisorArray = $user->getActiveSupervisor(WorkUserTypeConstant::CONSULTANT);
                break;
        }

        return $userSupervisorArray;
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
