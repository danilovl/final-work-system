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

namespace App\Domain\Work\Bus\Query\ActiveSupervisors;

use App\Infrastructure\Interfaces\Bus\QueryInterface;
use App\Domain\User\Entity\User;
use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage('sync')]
readonly class GetActiveSupervisorsQuery implements QueryInterface
{
    private function __construct(
        public User $user
    ) {}

    public static function create(User $user): self
    {
        return new self($user);
    }
}
