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
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\{
    TokenInterface,
    UsernamePasswordToken
};
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class TokenStorageUtilTest extends TestCase
{
    private Stub&TokenStorageInterface $tokenStorage;

    protected function setUp(): void
    {
        $this->tokenStorage = $this->createStub(TokenStorageInterface::class);
    }

    public function testRefreshToken(): void
    {
        /** @var Stub&UsernamePasswordToken $oldToken */
        $oldToken = $this->createStub(UsernamePasswordToken::class);
        $oldToken->method('getFirewallName')
            ->willReturn('firewall_name');

        $oldToken->method('getRoleNames')
            ->willReturn([UserRoleConstant::USER->value]);

        $this->tokenStorage->method('getToken')
            ->willReturn($oldToken);

        (new TokenStorageUtil($this->tokenStorage))->refreshToken(new User);
        $this->expectNotToPerformAssertions();
    }

    public function testRefreshTokenThrowsException(): void
    {
        /** @var Stub&UsernamePasswordToken $oldToken */
        $oldToken = $this->createStub(UsernamePasswordToken::class);

        $this->tokenStorage->method('getToken')
            ->willReturn($oldToken);

        $oldToken->method('getFirewallName')
            ->willReturn('');

        $this->expectException(InvalidArgumentException::class);
        (new TokenStorageUtil($this->tokenStorage))->refreshToken(new User);
    }

    public function testRefreshTokenThrowsMethodException(): void
    {
        /** @var Stub&TokenInterface $token */
        $token = $this->createStub(TokenInterface::class);

        $this->tokenStorage->method('getToken')
            ->willReturn($token);

        $this->expectException(RuntimeException::class);
        (new TokenStorageUtil($this->tokenStorage))->refreshToken(new User);
    }
}
