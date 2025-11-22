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

namespace App\Domain\EventAddress\Bus\Command\DeleteEventAddress;

use App\Application\Interfaces\Bus\CommandInterface;
use App\Domain\EventAddress\Entity\EventAddress;
use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage('sync')]
readonly class DeleteEventAddressCommand implements CommandInterface
{
    private function __construct(public EventAddress $eventAddress) {}

    public static function create(EventAddress $eventAddress): self
    {
        return new self($eventAddress);
    }
}
