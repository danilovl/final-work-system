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

use App\Domain\MediaType\Entity\MediaType;
use App\Domain\MediaType\Repository\MediaTypeRepository;

readonly class MediaTypeFacade
{
    public function __construct(private MediaTypeRepository $mediaTypeRepository) {}

    public function find(int $id): ?MediaType
    {
        return $this->mediaTypeRepository->find($id);
    }

    /**
     * @return MediaType[]
     */
    public function findAll(): array
    {
        return $this->mediaTypeRepository->findAll();
    }
}
