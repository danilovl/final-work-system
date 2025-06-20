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

namespace App\Tests\Unit\Domain\Security\Util;

use App\Application\Exception\RuntimeException;
use App\Domain\Security\Util\TokenStorageUtil;
use App\Domain\User\Constant\UserRoleConstant;
use App\Domain\User\Entity\User;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\{
    TokenInterface,
    UsernamePasswordToken
};
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class TokenStorageUtilTest extends TestCase
{
    public function testRefreshToken(): void
    {
        $tokenStorage = $this->createMock(TokenStorageInterface::class);

        $oldToken = $this->createMock(UsernamePasswordToken::class);
        $oldToken->expects($this->once())
            ->method('getFirewallName')
            ->willReturn('firewall_name');

        $oldToken->expects($this->once())
            ->method('getRoleNames')
            ->willReturn([UserRoleConstant::USER->value]);

        $tokenStorage->expects($this->once())
            ->method('getToken')
            ->willReturn($oldToken);

        $tokenStorage->expects($this->once())
            ->method('setToken')
            ->with($this->isInstanceOf(UsernamePasswordToken::class));

        (new TokenStorageUtil($tokenStorage))->refreshToken(new User);
    }

    public function testRefreshTokenThrowsException(): void
    {
        $tokenStorage = $this->createMock(TokenStorageInterface::class);
        $oldToken = $this->createMock(UsernamePasswordToken::class);

        $tokenStorage->expects($this->once())
            ->method('getToken')
            ->willReturn($oldToken);

        $oldToken->expects($this->once())
            ->method('getFirewallName')
            ->willReturn('');

        $this->expectException(InvalidArgumentException::class);
        (new TokenStorageUtil($tokenStorage))->refreshToken(new User);
    }

    public function testRefreshTokenThrowsMethodException(): void
    {
        $tokenStorage = $this->createMock(TokenStorageInterface::class);
        $token = $this->createMock(TokenInterface::class);

        $tokenStorage->expects($this->once())
            ->method('getToken')
            ->willReturn($token);

        $this->expectException(RuntimeException::class);
        (new TokenStorageUtil($tokenStorage))->refreshToken(new User);
    }
}
