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

namespace App\Domain\Version\Bus\Command\EditVersion;

use App\Infrastructure\Interfaces\Bus\{CommandBusInterface, CommandHandlerInterface};
use App\Domain\Media\Bus\Command\SaveMedia\SaveMediaCommand;
use App\Domain\Version\EventDispatcher\VersionEventDispatcherService;

readonly class EditVersionHandler implements CommandHandlerInterface
{
    public function __construct(
        private CommandBusInterface $commandBus,
        private VersionEventDispatcherService $versionEventDispatcherService,
    ) {}

    public function __invoke(EditVersionCommand $command): void
    {
        $media = $command->media;
        $saveMediaCommand = SaveMediaCommand::create(
            mediaModel: $command->mediaModel,
            media: $media
        );
        $this->commandBus->dispatchResult($saveMediaCommand);

        $media->setOwner($command->user);
        $this->versionEventDispatcherService->onVersionEdit($media);
    }
}
