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

namespace App\Model\Comment\Factory;

use App\Model\BaseModelFactory;
use App\Entity\Comment;
use App\Model\Comment\CommentModel;

class CommentFactory extends BaseModelFactory
{
    public function createFromModel(
        CommentModel $commentModel,
        Comment $comment = null
    ): Comment {
        $comment = $comment ?? new Comment;
        $comment = $this->fromModel($comment, $commentModel);

        $this->entityManagerService->persistAndFlush($comment);

        return $comment;
    }

    public function fromModel(
        Comment $comment,
        CommentModel $commentModel
    ): Comment {
        $comment->setContent($commentModel->content);
        $comment->setOwner($commentModel->owner);
        $comment->setEvent($commentModel->event);

        return $comment;
    }
}
