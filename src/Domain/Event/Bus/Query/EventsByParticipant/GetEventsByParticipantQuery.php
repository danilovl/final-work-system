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

namespace App\Domain\Event\Bus\Query\EventsByParticipant;

use App\Application\Interfaces\Bus\QueryInterface;
use App\Domain\Event\DTO\Repository\EventRepositoryDTO;
use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage('sync')]
readonly class GetEventsByParticipantQuery implements QueryInterface
{
    private function __construct(
        public EventRepositoryDTO $eventRepositoryDTO
    ) {}

    public static function create(EventRepositoryDTO $eventRepositoryDTO): self
    {
        return new self($eventRepositoryDTO);
    }
}
