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

namespace App\Domain\Profile\Bus\Command\ProfileChangePassword;

use App\Infrastructure\Interfaces\Bus\{CommandBusInterface, CommandHandlerInterface};
use App\Domain\User\Bus\Command\ChangeUserPassword\ChangeUserPasswordCommand;

readonly class ProfileChangePasswordHandler implements CommandHandlerInterface
{
    public function __construct(private CommandBusInterface $commandBus) {}

    public function __invoke(ProfileChangePasswordCommand $command): void
    {
        $changeUserPasswordCommand = ChangeUserPasswordCommand::create(
            user: $command->user,
            plainPassword: $command->plainPassword
        );

        $this->commandBus->dispatch($changeUserPasswordCommand);
    }
}
