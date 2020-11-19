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

namespace App\Model\Media;

use App\Entity\MediaType;
use App\Repository\MediaTypeRepository;

class MediaTypeFacade
{
    private MediaTypeRepository $mediaTypeRepository;

    public function __construct(MediaTypeRepository $mediaTypeRepository)
    {
        $this->mediaTypeRepository = $mediaTypeRepository;
    }

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
