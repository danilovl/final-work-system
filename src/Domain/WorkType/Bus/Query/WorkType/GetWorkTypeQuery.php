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

namespace App\Domain\WorkType\Bus\Query\WorkType;

use App\Application\Interfaces\Bus\QueryInterface;
use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage('sync')]
readonly class GetWorkTypeQuery implements QueryInterface
{
    private function __construct(
        public int $id
    ) {}

    public static function create(int $id): self
    {
        return new self($id);
    }
}
