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

namespace App\Domain\ApiUser\Bus\Query\ApiUserByKey;

use App\Infrastructure\Interfaces\Bus\QueryInterface;
use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage('sync')]
readonly class GetApiUserByKeyQuery implements QueryInterface
{
    private function __construct(
        public string $apiKey
    ) {}

    public static function create(string $apiKey): self
    {
        return new self($apiKey);
    }
}
