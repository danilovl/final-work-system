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

namespace App\Domain\User\Bus\Query\UserByEmail;

use App\Application\Interfaces\Bus\QueryInterface;
use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage('sync')]
readonly class GetUserByEmailQuery implements QueryInterface
{
    private function __construct(
        public string $email,
        public ?bool $enable
    ) {}

    public static function create(
        string $email,
        ?bool $enable
    ): self {
        return new self($email, $enable);
    }
}
