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

namespace App\Domain\Comment;

use App\Domain\Comment\Entity\Comment;
use App\Domain\Event\Entity\Event;
use App\Domain\Traits\ContentAwareTrait;
use App\Domain\User\Entity\User;

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
