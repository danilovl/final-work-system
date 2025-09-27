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

namespace App\Domain\DocumentCategory\Bus\Command\CreateDocumentCategory;

use App\Application\Interfaces\Bus\CommandHandlerInterface;
use App\Domain\MediaCategory\Factory\MediaCategoryFactory;

readonly class CreateDocumentCategoryHandler implements CommandHandlerInterface
{
    public function __construct(private MediaCategoryFactory $mediaCategoryFactory) {}

    public function __invoke(CreateDocumentCategoryCommand $command): void
    {
        $this->mediaCategoryFactory->flushFromModel($command->mediaCategoryModel);
    }
}
