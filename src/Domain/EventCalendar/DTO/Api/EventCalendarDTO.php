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

namespace App\Domain\EventCalendar\DTO\Api;

use Symfony\Component\Serializer\Attribute\Groups;

#[Groups(['event-calendar:read'])]
readonly class EventCalendarDTO
{
    public function __construct(
        public int $id,
        public string $title,
        public string $color,
        public string $start,
        public string $end,
        public bool $hasParticipant,
    ) {}
}
