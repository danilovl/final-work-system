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

namespace App\Domain\MediaCategory\Bus\Command\SaveMediaCategory;

use App\Infrastructure\Interfaces\Bus\CommandHandlerInterface;
use App\Domain\MediaCategory\Entity\MediaCategory;
use App\Domain\MediaCategory\Factory\MediaCategoryFactory;

readonly class SaveMediaCategoryHandler implements CommandHandlerInterface
{
    public function __construct(private MediaCategoryFactory $mediaCategoryFactory) {}

    public function __invoke(SaveMediaCategoryCommand $command): MediaCategory
    {
        return $this->mediaCategoryFactory->flushFromModel(
            mediaCategoryModel: $command->mediaCategoryModel,
            mediaCategory: $command->mediaCategory
        );
    }
}
