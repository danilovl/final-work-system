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

namespace App\Domain\WorkStatus\Bus\Query\WorkStatusList;

use App\Infrastructure\Interfaces\Bus\QueryInterface;
use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage('sync')]
readonly class GetWorkStatusListQuery implements QueryInterface
{
    private function __construct(
        public ?int $limit
    ) {}

    public static function create(?int $limit): self
    {
        return new self($limit);
    }
}
