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

namespace App\Domain\MediaCategory\Bus\Query\MediaCategoryListByOwners;

use App\Infrastructure\Interfaces\Bus\QueryInterface;
use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage('sync')]
readonly class GetMediaCategoryListByOwnersQuery implements QueryInterface
{
    private function __construct(
        public iterable $users
    ) {}

    public static function create(iterable $users): self
    {
        return new self($users);
    }
}
