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

namespace App\Domain\EventCalendar\DTO\Api\Output;

use App\Domain\EventAddress\DTO\Api\EventAddressDTO;
use App\Domain\EventParticipant\DTO\Api\EventParticipantDTO;
use App\Domain\EventType\DTO\Api\EventTypeDTO;
use Symfony\Component\Serializer\Attribute\Groups;

#[Groups(['output'])]
readonly class EventCalendarManageCreateDataOutput
{
    /**
     * @param EventTypeDTO[] $types
     * @param EventAddressDTO[] $addresses
     * @param EventParticipantDTO[] $participants
     */
    public function __construct(
        public array $types,
        public array $addresses,
        public array $participants
    ) {}
}
