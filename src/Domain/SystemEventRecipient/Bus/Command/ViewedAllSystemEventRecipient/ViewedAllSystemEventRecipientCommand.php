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

namespace App\Domain\SystemEventRecipient\Bus\Command\ViewedAllSystemEventRecipient;

use App\Infrastructure\Interfaces\Bus\CommandInterface;
use App\Domain\User\Entity\User;
use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage('sync')]
readonly class ViewedAllSystemEventRecipientCommand implements CommandInterface
{
    private function __construct(
        public User $recipient
    ) {}

    public static function create(User $recipient): self
    {
        return new self($recipient);
    }
}
