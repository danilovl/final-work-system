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

namespace App\Domain\MediaMimeType\Bus\Query\MediaMimeTypeListByOwner;

use App\Application\Interfaces\Bus\QueryInterface;
use App\Domain\MediaType\Entity\MediaType;
use App\Domain\User\Entity\User;
use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage('sync')]
readonly class GetMediaMimeTypeListByOwnerQuery implements QueryInterface
{
    private function __construct(
        public iterable|User $user,
        public iterable|MediaType|int|null $mediaType,
        public bool $onlyKey
    ) {}

    public static function create(
        iterable|User $user,
        iterable|MediaType|int|null $mediaType,
        bool $onlyKey
    ): self {
        return new self($user, $mediaType, $onlyKey);
    }
}
