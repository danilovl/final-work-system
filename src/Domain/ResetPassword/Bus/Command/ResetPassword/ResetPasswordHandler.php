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

namespace App\Domain\ResetPassword\Bus\Command\ResetPassword;

use App\Application\Interfaces\Bus\CommandHandlerInterface;
use App\Application\Service\EntityManagerService;
use App\Domain\ResetPassword\Service\ResetPasswordService;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

readonly class ResetPasswordHandler implements CommandHandlerInterface
{
    public function __construct(
        private EntityManagerService $entityManagerService,
        private ResetPasswordService $resetPasswordService,
        private UserPasswordHasherInterface $userPasswordHasher
    ) {}

    public function __invoke(ResetPasswordCommand $command): void
    {
        $user = $command->user;
        $token = $command->token;

        $this->resetPasswordService->removeResetRequest($token);

        $encodedPassword = $this->userPasswordHasher->hashPassword($user, $command->plainPassword);
        $user->setPassword($encodedPassword);
        
        $this->entityManagerService->flush();
    }
}