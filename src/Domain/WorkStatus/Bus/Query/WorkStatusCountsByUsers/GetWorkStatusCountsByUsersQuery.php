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

namespace App\Domain\WorkStatus\Bus\Query\WorkStatusCountsByUsers;

use App\Infrastructure\Interfaces\Bus\QueryInterface;
use App\Domain\User\Entity\User;
use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage('sync')]
readonly class GetWorkStatusCountsByUsersQuery implements QueryInterface
{
    private function __construct(
        public array $userIds,
        public User $supervisor,
        public string $type,
        public ?array $workStatus
    ) {}

    public static function create(
        array $userIds,
        User $supervisor,
        string $type,
        ?array $workStatus
    ): self {
        return new self($userIds, $supervisor, $type, $workStatus);
    }
}
