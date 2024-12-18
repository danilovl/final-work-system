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

namespace App\Domain\User\Service;

use App\Domain\User\Entity\User;
use App\Domain\User\Model\UserModel;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;

readonly class PasswordUpdater
{
    public function __construct(private PasswordHasherFactoryInterface $passwordHasherFactory) {}

    public function hashPassword(
        string $plainPassword,
        User $user,
        UserModel $userModel
    ): void {
        if (mb_strlen($plainPassword) === 0) {
            return;
        }

        $passwordHasher = $this->passwordHasherFactory->getPasswordHasher($user);
        $userModel->salt = '';

        $hashedPassword = $passwordHasher->hash($plainPassword);
        $userModel->password = $hashedPassword;
    }
}
