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

namespace App\Domain\Task\Bus\Query\ActiveTasksPreload;

use App\Application\Interfaces\Bus\QueryInterface;
use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage('sync')]
readonly class GetActiveTasksPreloadQuery implements QueryInterface
{
    private function __construct(
        public array $works
    ) {}

    public static function create(array $works): self
    {
        return new self($works);
    }
}
