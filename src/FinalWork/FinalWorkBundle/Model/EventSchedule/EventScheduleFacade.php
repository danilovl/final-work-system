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

namespace FinalWork\FinalWorkBundle\Model\EventSchedule;

use Doctrine\ORM\{
    Query,
    EntityManager
};
use FinalWork\FinalWorkBundle\Entity\EventSchedule;
use FinalWork\FinalWorkBundle\Entity\Repository\EventScheduleRepository;
use FinalWork\SonataUserBundle\Entity\User;

class EventScheduleFacade
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var EventScheduleRepository
     */
    private $eventScheduleRepository;

    /**
     * EventScheduleFacade constructor.
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->em = $entityManager;
        $this->eventScheduleRepository = $entityManager->getRepository(EventSchedule::class);
    }

    /**
     * @param User $user
     * @return Query
     */
    public function queryEventSchedulesByOwner(User $user): Query
    {
        return $this->eventScheduleRepository
            ->findAllByOwner($user)
            ->getQuery();
    }
}
