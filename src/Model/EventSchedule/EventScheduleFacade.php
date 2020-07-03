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

namespace App\Model\EventSchedule;

use Doctrine\ORM\Query;
use App\Repository\EventScheduleRepository;
use App\Entity\User;

class EventScheduleFacade
{
    private EventScheduleRepository $eventScheduleRepository;

    public function __construct(EventScheduleRepository $eventScheduleRepository)
    {
        $this->eventScheduleRepository = $eventScheduleRepository;
    }

    public function queryEventSchedulesByOwner(User $user): Query
    {
        return $this->eventScheduleRepository
            ->allByOwner($user)
            ->getQuery();
    }
}
