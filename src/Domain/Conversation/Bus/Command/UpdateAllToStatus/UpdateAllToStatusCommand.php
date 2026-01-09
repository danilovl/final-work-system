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

namespace App\Domain\Conversation\Bus\Command\UpdateAllToStatus;

use App\Application\Interfaces\Bus\CommandInterface;
use App\Domain\ConversationMessageStatusType\Entity\ConversationMessageStatusType;
use App\Domain\User\Entity\User;
use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage('sync')]
readonly class UpdateAllToStatusCommand implements CommandInterface
{
    private function __construct(public User $user, public ConversationMessageStatusType $type) {}

    public static function create(User $user, ConversationMessageStatusType $type): self
    {
        return new self($user, $type);
    }
}
