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

namespace App\Domain\Version\Bus\Command\CreateVersion;

use App\Application\Interfaces\Bus\{
    CommandBusInterface,
    CommandHandlerInterface
};
use App\Domain\Media\Bus\Command\SaveMedia\SaveMediaCommand;
use App\Domain\Media\Entity\Media;
use App\Domain\Version\EventDispatcher\VersionEventDispatcherService;

readonly class CreateVersionHandler implements CommandHandlerInterface
{
    public function __construct(
        private CommandBusInterface $commandBus,
        private VersionEventDispatcherService $versionEventDispatcherService,
    ) {}

    public function __invoke(CreateVersionCommand $command): void
    {
        $saveMediaCommand = SaveMediaCommand::create(
            mediaModel: $command->mediaModel,
            media: null
        );

        /** @var Media $media */
        $media = $this->commandBus->dispatchResult($saveMediaCommand);
        $this->versionEventDispatcherService->onVersionCreate($media);
    }
}
