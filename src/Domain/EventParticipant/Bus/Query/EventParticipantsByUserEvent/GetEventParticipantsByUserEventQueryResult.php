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

namespace App\Domain\EventParticipant\Bus\Query\EventParticipantsByUserEvent;

use App\Domain\EventParticipant\Entity\EventParticipant;

readonly class GetEventParticipantsByUserEventQueryResult
{
    /**
     * @param EventParticipant[] $eventParticipants
     */
    public function __construct(
        public array $eventParticipants
    ) {}
}
