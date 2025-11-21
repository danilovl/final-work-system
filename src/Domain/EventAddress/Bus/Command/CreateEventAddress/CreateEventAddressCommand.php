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

namespace App\Domain\EventAddress\Bus\Command\CreateEventAddress;

use App\Application\Interfaces\Bus\CommandInterface;
use App\Domain\EventAddress\Model\EventAddressModel;
use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage('sync')]
readonly class CreateEventAddressCommand implements CommandInterface
{
    private function __construct(public EventAddressModel $eventAddressModel) {}

    public static function create(EventAddressModel $eventAddressModel): self
    {
        return new self($eventAddressModel);
    }
}
