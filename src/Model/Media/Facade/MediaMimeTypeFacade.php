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

namespace App\Model\Media\Facade;

use App\Entity\MediaMimeType;
use App\Repository\MediaMimeTypeRepository;

class MediaMimeTypeFacade
{
    public function __construct(private MediaMimeTypeRepository $mediaMimeTypeRepository)
    {
    }

    public function getFormValidationMimeTypes(bool $onlyKey = false): array
    {
        $mimeTypes = $this->mediaMimeTypeRepository
            ->formValidationMimeTypeName()
            ->getQuery()
            ->getResult();

        if ($onlyKey === true) {
            return array_keys($mimeTypes);
        }

        return $mimeTypes;
    }

    public function getMimeTypesByOwner(
        $user,
        $mediaType = null,
        bool $onlyKey = false
    ): array {
        $mimeTypes = $this->mediaMimeTypeRepository
            ->allBy($user, $mediaType)
            ->getQuery()
            ->getResult();

        if ($onlyKey) {
            return array_keys($mimeTypes);
        }

        return $mimeTypes;
    }

    public function getMimeTypeByName(string $name): ?MediaMimeType
    {
        return $this->mediaMimeTypeRepository
            ->byName($name)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
