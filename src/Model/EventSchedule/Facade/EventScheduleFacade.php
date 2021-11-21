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

namespace App\Model\EventSchedule\Facade;

use App\Model\EventSchedule\Repository\EventScheduleRepository;
use Doctrine\ORM\Query;
use App\Model\User\Entity\User;

class EventScheduleFacade
{
    public function __construct(private EventScheduleRepository $eventScheduleRepository)
    {
    }

    public function queryEventSchedulesByOwner(User $user): Query
    {
        return $this->eventScheduleRepository
            ->allByOwner($user)
            ->getQuery();
    }
}
