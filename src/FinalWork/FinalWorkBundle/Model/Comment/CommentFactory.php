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

use FinalWork\FinalWorkBundle\Model\BaseModelFactory;
use Doctrine\ORM\{
    ORMException,
    OptimisticLockException
};
use FinalWork\FinalWorkBundle\Entity\Comment;

class CommentFactory extends BaseModelFactory
{
    /**
     * @param CommentModel $commentModel
     * @param Comment|null $comment
     * @return Comment|null
     *
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function createFromModel(CommentModel $commentModel, ?Comment $comment): Comment
    {
        $comment = $comment ?? new Comment;
        $comment = $this->fromModel($comment, $commentModel);

        $this->em->persist($comment);
        $this->em->flush();

        return $comment;
    }

    /**
     * @param Comment $comment
     * @param CommentModel $commentModel
     * @return Comment
     */
    public function fromModel(Comment $comment, CommentModel $commentModel): Comment
    {
        $comment->setContent($commentModel->content);
        $comment->setOwner($commentModel->owner);
        $comment->setEvent($commentModel->event);

        return $comment;
    }
}
