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

namespace App\Domain\Media\Facade;

use App\Domain\MediaMimeType\Entity\MediaMimeType;
use App\Domain\MediaMimeType\Repository\MediaMimeTypeRepository;
use App\Domain\MediaType\Entity\MediaType;
use App\Domain\User\Entity\User;

readonly class MediaMimeTypeFacade
{
    public function __construct(private MediaMimeTypeRepository $mediaMimeTypeRepository) {}

    /**
     * @return MediaMimeType[]|string[]
     */
    public function list(bool $onlyKey = false): array
    {
        /** @var MediaMimeType[] $mimeTypes */
        $mimeTypes = $this->mediaMimeTypeRepository
            ->formValidationMimeTypeName()
            ->getQuery()
            ->getResult();

        if ($onlyKey === true) {
            return array_keys($mimeTypes);
        }

        return $mimeTypes;
    }

    /**
     * @return MediaMimeType[]|string[]
     */
    public function listByOwner(
        iterable|User $user,
        iterable|MediaType|int|null $mediaType = null,
        bool $onlyKey = false
    ): array {
        /** @var MediaMimeType[] $mimeTypes */
        $mimeTypes = $this->mediaMimeTypeRepository
            ->allBy($user, $mediaType)
            ->getQuery()
            ->getResult();

        if ($onlyKey) {
            return array_keys($mimeTypes);
        }

        return $mimeTypes;
    }

    public function listByName(string $name): ?MediaMimeType
    {
        /** @var MediaMimeType|null $result */
        $result = $this->mediaMimeTypeRepository
            ->byName($name)
            ->getQuery()
            ->getOneOrNullResult();

        return $result;
    }
}
