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

namespace App\Domain\Conversation\Bus\Command\CreateWorkConversation;

use App\Application\Interfaces\Bus\CommandInterface;
use App\Domain\User\Entity\User;
use App\Domain\Work\Entity\Work;
use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage('sync')]
readonly class CreateWorkConversationCommand implements CommandInterface
{
    private function __construct(
        public User $userOne,
        public User $userTwo,
        public int $type,
        public Work $work
    ) {}

    public static function create(User $userOne, User $userTwo, int $type, Work $work): self
    {
        return new self($userOne, $userTwo, $type, $work);
    }
}
