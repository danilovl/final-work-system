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

namespace App\Domain\Document\Bus\Command\EditDocument;

use App\Infrastructure\Interfaces\Bus\{CommandBusInterface, CommandHandlerInterface};
use App\Domain\Media\Bus\Command\SaveMedia\SaveMediaCommand;

readonly class EditDocumentHandler implements CommandHandlerInterface
{
    public function __construct(private CommandBusInterface $commandBus) {}

    public function __invoke(EditDocumentCommand $command): void
    {
        $saveMediaCommand = SaveMediaCommand::create(
            mediaModel: $command->mediaModel,
            media: $command->media
        );

        $this->commandBus->dispatchResult($saveMediaCommand);
    }
}
