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

namespace App\Domain\EventSchedule\Command\CreateEventSchedule;

use App\Application\Interfaces\Bus\CommandInterface;
use App\Domain\EventSchedule\Model\EventScheduleModel;
use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage('sync')]
readonly class CreateEventScheduleCommand implements CommandInterface
{
    private function __construct(public EventScheduleModel $eventScheduleModel) {}

    public static function create(EventScheduleModel $eventScheduleModel): self
    {
        return new self($eventScheduleModel);
    }
}
