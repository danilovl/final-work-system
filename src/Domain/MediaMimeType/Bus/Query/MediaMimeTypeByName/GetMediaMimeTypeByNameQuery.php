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

namespace App\Domain\MediaMimeType\Bus\Query\MediaMimeTypeByName;

use App\Application\Interfaces\Bus\QueryInterface;
use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage('sync')]
readonly class GetMediaMimeTypeByNameQuery implements QueryInterface
{
    private function __construct(
        public ?string $name
    ) {}

    public static function create(?string $name): self
    {
        return new self($name);
    }
}
