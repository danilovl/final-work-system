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

namespace App\Domain\User\Bus\Command\CreateUser;

use App\Application\Interfaces\Bus\CommandHandlerInterface;
use App\Domain\User\Entity\User;
use App\Domain\User\EventDispatcher\UserEventDispatcher;
use App\Domain\User\Facade\UserFacade;
use App\Domain\User\Factory\UserFactory;

readonly class CreateUserHandler implements CommandHandlerInterface
{
    public function __construct(
        private UserFacade $userFacade,
        private UserFactory $userFactory,
        private UserEventDispatcher $userEventDispatcher
    ) {}

    public function __invoke(CreateUserCommand $command): ?User
    {
        $userModel = $command->userModel;
        $email = $userModel->email;
        $username = $userModel->username;

        if ($this->userFacade->findByUsername($username) || $this->userFacade->findByEmail($email)) {
            return null;
        }

        $user = $this->userFactory->createNewUser($userModel);
        $this->userEventDispatcher->onUserCreate($user);

        return $user;
    }
}
