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

namespace App\Domain\ConversationMessage\Bus\Query\ConversationMessageQueryByWorkUser;

use App\Infrastructure\Interfaces\Bus\QueryInterface;
use App\Domain\User\Entity\User;
use App\Domain\Work\Entity\Work;
use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage('sync')]
readonly class GetConversationMessageQueryByWorkUserQuery implements QueryInterface
{
    private function __construct(
        public Work $work,
        public User $user
    ) {}

    public static function create(
        Work $work,
        User $user
    ): self {
        return new self($work, $user);
    }
}
