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

use App\Application\Interfaces\Bus\{
    CommandBusInterface,
    CommandHandlerInterface
};
use App\Domain\MediaCategory\Bus\Command\SaveMediaCategory\SaveMediaCategoryCommand;

readonly class CreateDocumentCategoryHandler implements CommandHandlerInterface
{
    public function __construct(private CommandBusInterface $commandBus) {}

    public function __invoke(CreateDocumentCategoryCommand $command): void
    {
        $saveMediaCategoryCommand = SaveMediaCategoryCommand::create(
            mediaCategoryModel: $command->mediaCategoryModel,
            mediaCategory: null
        );

        $this->commandBus->dispatchResult($saveMediaCategoryCommand);
    }
}
