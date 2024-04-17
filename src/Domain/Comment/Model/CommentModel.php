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

namespace App\Domain\Comment\Model;

use App\Domain\Comment\Entity\Comment;
use App\Domain\Event\Entity\Event;
use App\Domain\User\Entity\User;

class CommentModel
{
    public function __construct(
        public User $owner,
        public Event $event,
        public string $content = ''
    ) {}

    public static function fromComment(Comment $comment): self
    {
        return new self(
            $comment->getOwner(),
            $comment->getEvent(),
            $comment->getContent()
        );
    }
}
