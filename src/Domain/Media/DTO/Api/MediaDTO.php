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

namespace App\Domain\Media\DTO\Api;

use App\Domain\MediaType\DTO\Api\MediaTypeDTO;
use App\Domain\User\DTO\Api\UserDTO;
use App\Domain\Work\DTO\Api\WorkDetailDTO;

readonly class MediaDTO
{
    public function __construct(
        public int $id,
        public MediaTypeDTO $type,
        public string $name,
        public ?string $description,
        public string $mediaName,
        public ?int $mediaSize,
        public ?string $originalExtension,
        public UserDTO $owner,
        public ?WorkDetailDTO $work
    ) {}
}
