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
use Webmozart\Assert\Assert;

readonly class MediaTypeFacade
{
    public function __construct(private MediaTypeRepository $mediaTypeRepository) {}

    public function find(int $id): ?MediaType
    {
        /** @var MediaType|null $result */
        $result = $this->mediaTypeRepository->find($id);

        return $result;
    }

    /**
     * @return MediaType[]
     */
    public function findAll(): array
    {
        /** @var array $result */
        $result = $this->mediaTypeRepository->findAll();

        Assert::allIsInstanceOf($result, MediaType::class);

        return $result;
    }
}
