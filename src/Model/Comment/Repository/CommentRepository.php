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

namespace App\Model\Comment\Repository;

use App\Model\Comment\Entity\Comment;
use App\Model\Event\Entity\Event;
use App\Model\User\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

class CommentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Comment::class);
    }

    private function baseQueryBuilder(): QueryBuilder
    {
        return $this->createQueryBuilder('comment')
            ->setCacheable(true);
    }

    public function allByOwnerEvent(
        User $user,
        Event $event
    ): QueryBuilder {
        return $this->baseQueryBuilder()
            ->where('comment.event = :event')
            ->andWhere('comment.owner = :user')
            ->setParameter('user', $user)
            ->setParameter('event', $event);
    }
}
