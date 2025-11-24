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

use App\Application\Interfaces\Bus\CommandHandlerInterface;
use App\Domain\EventSchedule\Entity\EventSchedule;
use App\Domain\EventSchedule\Factory\EventScheduleFactory;

readonly class CreateEventScheduleHandler implements CommandHandlerInterface
{
    public function __construct(private EventScheduleFactory $eventScheduleFactory) {}

    public function __invoke(CreateEventScheduleCommand $command): EventSchedule
    {
        return $this->eventScheduleFactory->flushFromModel($command->eventScheduleModel);
    }
}
