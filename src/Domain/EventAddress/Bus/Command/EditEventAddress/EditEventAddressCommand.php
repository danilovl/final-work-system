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

namespace App\Domain\EventAddress\Bus\Command\EditEventAddress;

use App\Application\Interfaces\Bus\CommandInterface;
use App\Domain\EventAddress\Entity\EventAddress;
use App\Domain\EventAddress\Model\EventAddressModel;
use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage('sync')]
readonly class EditEventAddressCommand implements CommandInterface
{
    private function __construct(
        public EventAddressModel $eventAddressModel,
        public EventAddress $eventAddress
    ) {}

    public static function create(
        EventAddressModel $eventAddressModel,
        EventAddress $eventAddress
    ): self {
        return new self($eventAddressModel, $eventAddress);
    }
}
