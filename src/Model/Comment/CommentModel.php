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

namespace App\Model\Comment;

use App\Entity\{
    Event,
    Comment
};
use App\Model\Traits\ContentAwareTrait;
use App\Entity\User;

class CommentModel
{
    use ContentAwareTrait;

    public ?User $owner = null;
    public ?Event $event = null;

    public static function fromComment(Comment $comment): self
    {
        $model = new self;
        $model->content = $comment->getContent();
        $model->owner = $comment->getOwner();
        $model->event = $comment->getEvent();

        return $model;
    }
}
