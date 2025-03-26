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

namespace App\Application\Util;

use App\Application\Exception\RuntimeException;
use App\Domain\User\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class TokenStorageUtil
{
    public function __construct(private TokenStorageInterface $tokenStorage) {}

    public function refreshToken(User $user): void
    {
        $oldToken = $this->tokenStorage->getToken();

        if ($oldToken === null || !method_exists($oldToken, 'getFirewallName')) {
            throw new RuntimeException('Method not exist');
        }

        $token = new UsernamePasswordToken(
            $user,
            $oldToken->getFirewallName(),
            $oldToken->getRoleNames()
        );

        $this->tokenStorage->setToken($token);
    }
}
