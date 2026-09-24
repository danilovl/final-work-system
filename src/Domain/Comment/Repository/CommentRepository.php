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

namespace App\Domain\Comment\Repository;

use App\Domain\Comment\Entity\Comment;
use App\Domain\Event\Entity\Event;
use App\Domain\User\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

class CommentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Comment::class);
    }

    private function createCommentQueryBuilder(): CommentQueryBuilder
    {
        return new CommentQueryBuilder($this->createQueryBuilder('comment'));
    }

    public function allByOwnerEvent(User $user, Event $event): QueryBuilder
    {
        return $this->createCommentQueryBuilder()
            ->whereByEvent($event)
            ->whereByOwner($user)
            ->getQueryBuilder();
    }
}
