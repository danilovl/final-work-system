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

namespace App\Domain\User\Bus\Command\ChangeUserPassword;

use App\Infrastructure\Interfaces\Bus\CommandHandlerInterface;
use App\Domain\User\Model\UserModel;
use App\Domain\User\Service\PasswordUpdater;
use App\Infrastructure\Service\EntityManagerService;

readonly class ChangeUserPasswordHandler implements CommandHandlerInterface
{
    public function __construct(
        private PasswordUpdater $passwordUpdater,
        private EntityManagerService $entityManagerService
    ) {}

    public function __invoke(ChangeUserPasswordCommand $command): void
    {
        $user = $command->user;
        $userModel = UserModel::fromUser($user);

        $this->passwordUpdater->hashPassword(
            plainPassword: $command->plainPassword,
            user: $user,
            userModel: $userModel
        );

        $user->setPassword($userModel->password);
        $user->setSalt($userModel->salt);

        $this->entityManagerService->flush();
    }
}
