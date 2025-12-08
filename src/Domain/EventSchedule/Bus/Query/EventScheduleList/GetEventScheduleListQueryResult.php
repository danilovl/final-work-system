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

namespace App\Domain\EventSchedule\Bus\Query\EventScheduleList;

use App\Domain\EventSchedule\Entity\EventSchedule;
use Knp\Component\Pager\Pagination\PaginationInterface;

readonly class GetEventScheduleListQueryResult
{
    /**
     * @param PaginationInterface<int, EventSchedule> $eventSchedules
     */
    public function __construct(public PaginationInterface $eventSchedules) {}
}
