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

namespace App\Domain\EventSchedule\Command\DeleteEventSchedule;

use App\Application\Interfaces\Bus\CommandInterface;
use App\Domain\EventSchedule\Entity\EventSchedule;
use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage('sync')]
readonly class DeleteEventScheduleCommand implements CommandInterface
{
    private function __construct(public EventSchedule $eventSchedule) {}

    public static function create(EventSchedule $eventSchedule): self
    {
        return new self($eventSchedule);
    }
}
