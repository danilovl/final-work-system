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

namespace App\Domain\Work\Bus\Command\EditAuthor;

use App\Infrastructure\Interfaces\Bus\{CommandBusInterface, CommandHandlerInterface};
use App\Domain\User\Bus\Command\SaveUser\SaveUserCommand;
use App\Domain\Work\EventDispatcher\WorkEventDispatcher;

readonly class EditAuthorHandler implements CommandHandlerInterface
{
    public function __construct(
        private CommandBusInterface $commandBus,
        private WorkEventDispatcher $workEventDispatcher
    ) {}

    public function __invoke(EditAuthorCommand $command): void
    {
        $saveUserCommand = SaveUserCommand::create(
            userModel: $command->userModel,
            user: $command->user
        );
        $this->commandBus->dispatch($saveUserCommand);

        $this->workEventDispatcher->onWorkEditAuthor($command->work);
    }
}
