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

namespace App\Domain\Document\Bus\Command\CreateDocument;

use App\Infrastructure\Interfaces\Bus\{CommandBusInterface, CommandHandlerInterface};
use App\Domain\Document\EventDispatcher\DocumentEventDispatcher;
use App\Domain\Media\Bus\Command\SaveMedia\SaveMediaCommand;
use App\Domain\Media\Entity\Media;

readonly class CreateDocumentHandler implements CommandHandlerInterface
{
    public function __construct(
        private CommandBusInterface $commandBus,
        private DocumentEventDispatcher $documentEventDispatcher
    ) {}

    public function __invoke(CreateDocumentCommand $command): void
    {
        $saveMediaCommand = SaveMediaCommand::create(
            mediaModel: $command->mediaModel,
            media: null
        );

        /** @var Media $media */
        $media = $this->commandBus->dispatchResult($saveMediaCommand);
        $this->documentEventDispatcher->onDocumentCreate($media);
    }
}
