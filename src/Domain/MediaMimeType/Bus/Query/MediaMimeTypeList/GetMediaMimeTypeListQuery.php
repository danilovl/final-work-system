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

namespace App\Domain\MediaMimeType\Bus\Query\MediaMimeTypeList;

use App\Infrastructure\Interfaces\Bus\QueryInterface;
use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage('sync')]
readonly class GetMediaMimeTypeListQuery implements QueryInterface
{
    private function __construct(
        public bool $onlyKey
    ) {}

    public static function create(bool $onlyKey): self
    {
        return new self($onlyKey);
    }
}
