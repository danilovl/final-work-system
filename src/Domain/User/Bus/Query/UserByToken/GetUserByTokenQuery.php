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

namespace App\Domain\User\Bus\Query\UserByToken;

use App\Application\Interfaces\Bus\QueryInterface;
use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage('sync')]
readonly class GetUserByTokenQuery implements QueryInterface
{
    private function __construct(
        public string $username,
        public string $token
    ) {}

    public static function create(
        string $username,
        string $token
    ): self {
        return new self($username, $token);
    }
}
