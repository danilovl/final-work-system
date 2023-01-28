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

namespace App\Domain\Comment\Facade;

use App\Domain\Comment\Entity\Comment;
use App\Domain\Comment\Repository\CommentRepository;
use App\Domain\Event\Entity\Event;
use App\Domain\User\Entity\User;

readonly class CommentFacade
{
    public function __construct(private CommentRepository $commentRepository) {}

    public function getCommentByOwnerEvent(
        User $user,
        Event $event
    ): ?Comment {
        return $this->commentRepository
            ->allByOwnerEvent($user, $event)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
