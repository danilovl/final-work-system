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

namespace App\Domain\MediaMimeType\Facade;

use App\Domain\MediaMimeType\Entity\MediaMimeType;
use App\Domain\MediaMimeType\Repository\MediaMimeTypeRepository;

readonly class MediaMimeTypeFacade
{
    public function __construct(private MediaMimeTypeRepository $mediaMimeTypeRepository) {}

    public function findByName(?string $name): ?MediaMimeType
    {
        if ($name === null) {
            return null;
        }

        /** @var MediaMimeType|null $result */
        $result = $this->mediaMimeTypeRepository
            ->byName($name)
            ->getQuery()
            ->getOneOrNullResult();

        return $result;
    }
}
