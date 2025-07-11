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

namespace App\Domain\EventSchedule\Facade;

use App\Domain\EventSchedule\Repository\EventScheduleRepository;
use Doctrine\ORM\Query;
use App\Domain\User\Entity\User;

readonly class EventScheduleFacade
{
    public function __construct(private EventScheduleRepository $eventScheduleRepository) {}

    public function queryByOwner(User $user): Query
    {
        return $this->eventScheduleRepository
            ->allByOwner($user)
            ->getQuery();
    }
}
