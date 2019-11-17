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

namespace FinalWork\FinalWorkBundle\Model\EventAddress;

use Doctrine\ORM\{
    EntityManager,
    NonUniqueResultException
};
use FinalWork\FinalWorkBundle\Entity\EventAddress;
use FinalWork\FinalWorkBundle\Entity\Repository\EventAddressRepository;
use FinalWork\SonataUserBundle\Entity\User;

class EventAddressFacade
{
    /**
     * @var EventAddressRepository
     */
    private $eventAddressRepository;

    /**
     * EventAddressFacade constructor.
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->eventAddressRepository = $entityManager->getRepository(EventAddress::class);
    }

    /**
     * @param User $user
     * @return EventAddress
     *
     * @throws NonUniqueResultException
     */
    public function getSkypeByOwner(User $user): ?EventAddress
    {
        return $this->eventAddressRepository
            ->findSkypeByOwner($user)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
