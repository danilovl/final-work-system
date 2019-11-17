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
    Query,
    EntityManager
};
use FinalWork\FinalWorkBundle\Entity\SystemEventRecipient;
use FinalWork\FinalWorkBundle\Entity\Repository\SystemEventRecipientRepository;
use FinalWork\SonataUserBundle\Entity\User;

class SystemEventRecipientFacade
{
    /**
     * @var SystemEventRecipientRepository
     */
    private $systemEventRecipientRepository;

    /**
     * SystemEventRecipientFacade constructor.
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->systemEventRecipientRepository = $entityManager->getRepository(SystemEventRecipient::class);
    }

    /**
     * @param User $user
     * @return Query
     */
    public function queryRecipientsByUser(User $user): Query
    {
        return $this->systemEventRecipientRepository
            ->findAllByRecipient($user)
            ->getQuery();
    }

    /**
     * @param User $user
     * @param int $limit
     * @return array
     */
    public function getUnreadSystemEventsByRecipient(User $user, int $limit = null): array
    {
        $systemEvents = $this->systemEventRecipientRepository
            ->findAllUnreadByRecipient($user);

        if ($limit !== null) {
            $systemEvents->setMaxResults($limit);
        }

        return $systemEvents->getQuery()->getResult();
    }

    /**
     * @param User $recipient
     * @return Query
     */
    public function queryRecipientsQueryByUser(User $recipient): Query
    {
        return $this->systemEventRecipientRepository
            ->findAllByRecipient($recipient)
            ->getQuery();
    }
}
