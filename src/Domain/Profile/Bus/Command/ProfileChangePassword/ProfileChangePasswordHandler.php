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

use App\Application\Interfaces\Bus\CommandHandlerInterface;
use App\Domain\User\Factory\UserFactory;
use App\Domain\User\Model\UserModel;
use App\Domain\User\Service\PasswordUpdater;

readonly class ProfileChangePasswordHandler implements CommandHandlerInterface
{
    public function __construct(
        private PasswordUpdater $passwordUpdater,
        private UserFactory $userFactory
    ) {}

    public function __invoke(ProfileChangePasswordCommand $command): void
    {
        $userModel = UserModel::fromUser($command->user);

        $this->passwordUpdater->hashPassword(
            $command->plainPassword,
            $command->user,
            $userModel
        );

        $this->userFactory->flushFromModel($userModel, $command->user);
    }
}
