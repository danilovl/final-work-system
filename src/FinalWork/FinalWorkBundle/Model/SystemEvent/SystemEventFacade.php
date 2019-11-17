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

namespace FinalWork\FinalWorkBundle\Model\SystemEvent;

use Doctrine\ORM\{
    EntityManager,
    NonUniqueResultException
};
use FinalWork\FinalWorkBundle\Entity\SystemEvent;
use FinalWork\FinalWorkBundle\Entity\Repository\SystemEventRepository;
use FinalWork\SonataUserBundle\Entity\User;

class SystemEventFacade
{
    /**
     * @var SystemEventRepository
     */
    private $systemEventRepository;

    /**
     * SystemEventFacade constructor.
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->systemEventRepository = $entityManager->getRepository(SystemEvent::class);
    }

    /**
     * @param User $user
     * @param int $limit
     * @return array
     */
    public function getUnreadSystemEventsByRecipient(User $user, int $limit = null): array
    {
        $systemEvents = $this->systemEventRepository
            ->findAllByRecipient($user);

        if ($limit !== null) {
            $systemEvents->setMaxResults($limit);
        }

        return $systemEvents->getQuery()->getResult();
    }

    /**
     * @param User $user
     * @return int|null
     *
     * @throws NonUniqueResultException
     */
    public function getTotalUnreadSystemEventsByRecipient(User $user): ?int
    {
        return (int)$this->systemEventRepository
            ->getCountUnreadByRecipient($user)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
