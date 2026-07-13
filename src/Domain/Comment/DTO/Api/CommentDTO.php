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

namespace App\Domain\Comment\DTO\Api;

use App\Domain\User\DTO\Api\UserDTO;
use Symfony\Component\Serializer\Attribute\Groups;

readonly class CommentDTO
{
    public function __construct(
        #[Groups(['comment:read'])]
        public int $id,
        #[Groups(['comment:read'])]
        public string $content,
        #[Groups(['comment:owner:read'])]
        public UserDTO $owner,
        #[Groups(['comment:read'])]
        public string $createdAt,
        #[Groups(['comment:read'])]
        public ?string $updatedAt = null
    ) {}
}
