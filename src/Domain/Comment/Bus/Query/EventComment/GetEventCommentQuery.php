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

namespace App\Domain\Comment\Bus\Query\EventComment;

use App\Infrastructure\Interfaces\Bus\QueryInterface;
use App\Domain\Event\Entity\Event;
use App\Domain\User\Entity\User;
use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage('sync')]
readonly class GetEventCommentQuery implements QueryInterface
{
    private function __construct(
        public User $user,
        public Event $event
    ) {}

    public static function create(
        User $user,
        Event $event
    ): self {
        return new self($user, $event);
    }
}
