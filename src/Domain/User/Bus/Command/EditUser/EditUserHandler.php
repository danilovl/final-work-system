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

namespace App\Domain\User\Bus\Command\EditUser;

use App\Application\Interfaces\Bus\CommandHandlerInterface;
use App\Domain\User\EventDispatcher\UserEventDispatcher;
use App\Domain\User\Factory\UserFactory;
use App\Domain\User\Service\UserService;

readonly class EditUserHandler implements CommandHandlerInterface
{
    public function __construct(
        private UserFactory $userFactory,
        private UserEventDispatcher $userEventDispatcher,
        private UserService $userService
    ) {}

    public function __invoke(EditUserCommand $command): void
    {
        $userModel = $command->userModel;
        $user = $command->user;

        $this->userFactory->flushFromModel($userModel, $user);

        $this->userEventDispatcher->onUserEdit(
            $user,
            $this->userService->getUser()
        );
    }
}
