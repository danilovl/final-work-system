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

use App\Application\Exception\UserNotExistException;
use App\Domain\User\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

readonly class UserService
{
    public function __construct(private TokenStorageInterface $tokenStorage) {}

    public function getUserOrNull(): ?User
    {
        $token = $this->tokenStorage->getToken();
        if ($token === null) {
            return null;
        }

        $user = $token->getUser();
        if (!is_object($user) || !$user instanceof User) {
            return null;
        }

        return $user;
    }

    public function getUser(): User
    {
        $user = $this->getUserOrNull();
        if ($user === null) {
            throw new UserNotExistException('User must exist.');
        }

        return $user;
    }
}
