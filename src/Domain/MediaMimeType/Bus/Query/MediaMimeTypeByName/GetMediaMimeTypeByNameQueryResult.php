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

use App\Domain\MediaMimeType\Entity\MediaMimeType;

readonly class GetMediaMimeTypeByNameQueryResult
{
    public function __construct(
        public ?MediaMimeType $mediaMimeType
    ) {}
}
