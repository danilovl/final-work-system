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

namespace App\Domain\SystemEventRecipient\Bus\Query\SystemEventsQueryByStatus;

use App\Application\Interfaces\Bus\QueryInterface;
use App\Domain\SystemEvent\DTO\Repository\SystemEventRepositoryDTO;
use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage('sync')]
readonly class GetSystemEventsQueryByStatusQuery implements QueryInterface
{
    private function __construct(
        public SystemEventRepositoryDTO $systemEventRepositoryDTO
    ) {}

    public static function create(SystemEventRepositoryDTO $systemEventRepositoryDTO): self
    {
        return new self($systemEventRepositoryDTO);
    }
}
