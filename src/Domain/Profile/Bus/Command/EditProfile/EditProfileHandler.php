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

namespace App\Domain\Profile\Bus\Command\EditProfile;

use App\Application\Interfaces\Bus\{
    CommandBusInterface,
    CommandHandlerInterface
};
use App\Domain\User\Bus\Command\SaveUser\SaveUserCommand;

readonly class EditProfileHandler implements CommandHandlerInterface
{
    public function __construct(private CommandBusInterface $commandBus) {}

    public function __invoke(EditProfileCommand $command): void
    {
        $saveUserCommand = SaveUserCommand::create(
            userModel: $command->userModel,
            user: $command->user
        );

        $this->commandBus->dispatch($saveUserCommand);
    }
}
