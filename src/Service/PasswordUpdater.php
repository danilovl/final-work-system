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

namespace App\Service;

use App\Entity\User;
use App\Model\User\UserModel;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;

class PasswordUpdater
{
    public function __construct(private EncoderFactoryInterface $encoderFactory)
    {
    }

    public function hashPassword(
        string $plainPassword,
        User $user,
        UserModel $userModel
    ): void {
        if (strlen($plainPassword) === 0) {
            return;
        }

        $encoder = $this->encoderFactory->getEncoder($user);
        $salt = rtrim(str_replace('+', '.', base64_encode(random_bytes(32))), '=');
        $userModel->salt = $salt;

        $hashedPassword = $encoder->encodePassword($plainPassword, $userModel->salt);
        $userModel->password = $hashedPassword;
    }
}
