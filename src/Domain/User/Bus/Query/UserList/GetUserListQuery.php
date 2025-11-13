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

namespace App\Domain\User\Bus\Query\UserList;

use App\Application\Interfaces\Bus\QueryInterface;
use App\Domain\User\Entity\User;
use App\Domain\WorkStatus\Entity\WorkStatus;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage('sync')]
readonly class GetUserListQuery implements QueryInterface
{
    /**
     * @param WorkStatus[]|null $workStatus
     */
    private function __construct(
        public Request $request,
        public User $user,
        public string $type,
        public ?array $workStatus = null
    ) {}

    /**
     * @param WorkStatus[]|null $workStatus
     */
    public static function create(
        Request $request,
        User $user,
        string $type,
        ?array $workStatus = null
    ): self {
        return new self(
            request: $request,
            user: $user,
            type: $type,
            workStatus: $workStatus
        );
    }
}
