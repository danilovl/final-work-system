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

namespace App\Domain\EventSchedule\Command\CloneEventSchedule;

use App\Application\Interfaces\Bus\CommandInterface;
use App\Domain\EventSchedule\Entity\EventSchedule;
use App\Domain\EventSchedule\Model\EventScheduleCloneModel;
use App\Domain\User\Entity\User;
use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage('sync')]
readonly class CloneEventScheduleCommand implements CommandInterface
{
    private function __construct(
        public User $user,
        public EventSchedule $eventSchedule,
        public EventScheduleCloneModel $eventScheduleCloneModel
    ) {}

    public static function create(
        User $user,
        EventSchedule $eventSchedule,
        EventScheduleCloneModel $eventScheduleCloneModel
    ): self {
        return new self($user, $eventSchedule, $eventScheduleCloneModel);
    }
}
