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

namespace App\Domain\Work\Bus\Query\UserWorks;

use App\Application\Interfaces\Bus\QueryInterface;
use App\Domain\User\Entity\User;
use App\Domain\WorkStatus\Entity\WorkStatus;
use App\Domain\WorkType\Entity\WorkType;
use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage('sync')]
readonly class GetUserWorksQuery implements QueryInterface
{
    private function __construct(
        public User $user,
        public string $userType,
        public ?WorkType $type,
        public ?WorkStatus $status
    ) {}

    public static function create(
        User $user,
        string $userType,
        ?WorkType $type,
        ?WorkStatus $status
    ): self {
        return new self($user, $userType, $type, $status);
    }
}
