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

namespace App\Domain\Event\Bus\Command\CreateEvent;

use App\Infrastructure\Interfaces\Bus\CommandInterface;
use App\Domain\Event\Model\EventModel;
use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage('sync')]
readonly class CreateEventCommand implements CommandInterface
{
    private function __construct(
        public EventModel $eventModel
    ) {}

    public static function create(EventModel $eventModel): self
    {
        return new self($eventModel);
    }
}
