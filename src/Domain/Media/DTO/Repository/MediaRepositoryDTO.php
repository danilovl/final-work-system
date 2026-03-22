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

namespace App\Domain\Media\DTO\Repository;

use App\Domain\MediaType\Entity\MediaType;

class MediaRepositoryDTO
{
    public function __construct(
        public mixed $users = null,
        public ?bool $active = null,
        public MediaType|iterable|null $type = null,
        public ?array $criteria = null
    ) {}
}
