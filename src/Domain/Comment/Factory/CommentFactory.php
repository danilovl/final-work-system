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

namespace App\Domain\Comment\Factory;

use App\Application\Factory\Model\BaseModelFactory;
use App\Domain\Comment\Entity\Comment;
use App\Domain\Comment\Model\CommentModel;

class CommentFactory extends BaseModelFactory
{
    public function createFromModel(
        CommentModel $commentModel,
        Comment $comment = null
    ): Comment {
        $comment ??= new Comment;
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
