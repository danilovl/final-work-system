<?php declare(strict_types=1);

/*
 *
 * This file is part of the FinalWorkSystem project.
 * (c) Vladimir Danilov
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace App\Repository;

use App\Entity\{
    User,
    EventAddress
};
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\QueryBuilder;

class EventAddressRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EventAddress::class);
    }

    private function baseQueryBuilder(): QueryBuilder
    {
        return $this->createQueryBuilder('event_address')
            ->setCacheable(true);
    }

    public function skypeByOwner(User $user): QueryBuilder
    {
        return $this->baseQueryBuilder()
            ->where('event_address.skype = :skype')
            ->andWhere('event_address.owner = :user')
            ->setParameter('skype', true)
            ->setParameter('user', $user);
    }
}
