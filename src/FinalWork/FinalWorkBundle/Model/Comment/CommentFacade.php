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

namespace FinalWork\FinalWorkBundle\Model\Comment;

use Doctrine\ORM\{
    EntityManager,
    NonUniqueResultException
};
use FinalWork\FinalWorkBundle\Entity\{
    Event,
    Comment
};
use FinalWork\FinalWorkBundle\Entity\Repository\CommentRepository;
use FinalWork\SonataUserBundle\Entity\User;

class CommentFacade
{
    /**
     * @var CommentRepository
     */
    private $commentRepository;

    /**
     * CommentFacade constructor.
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->commentRepository = $entityManager->getRepository(Comment::class);
    }

    /**
     * @param User $user
     * @param Event $event
     * @return Comment|null
     *
     * @throws NonUniqueResultException
     */
    public function getCommentByOwnerEvent(
        User $user,
        Event $event
    ): ?Comment {
        return $this->commentRepository
            ->findAllByOwnerEvent($user, $event)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
