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

namespace App\Tests\Unit\Application\Security;

use App\Application\Security\Provider\AppUserProvider;
use App\Application\Service\EntityManagerService;
use App\Domain\User\Entity\User;
use App\Domain\User\Facade\UserFacade;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;

class AppUserProviderTest extends TestCase
{
    private MockObject $userFacade;

    private MockObject $entityManagerService;

    private AppUserProvider $appUserProvider;

    protected function setUp(): void
    {
        $this->userFacade = $this->createMock(UserFacade::class);
        $this->entityManagerService = $this->createMock(EntityManagerService::class);

        $this->appUserProvider = new AppUserProvider(
            $this->userFacade,
            $this->entityManagerService
        );
    }

    public function testUpgradePassword(): void
    {
        $this->entityManagerService
            ->expects($this->once())
            ->method('flush');

        $this->appUserProvider->upgradePassword(new User, 'newHashedPassword');

        $this->assertTrue(true);
    }

    public function testLoadUserByIdentifierSuccess(): void
    {
        $user = new User;

        $this->userFacade
            ->expects($this->once())
            ->method('findOneByUsername')
            ->willReturn($user);

        $result = $this->appUserProvider->loadUserByIdentifier('identifier');

        $this->assertSame($user, $result);
    }

    public function testLoadUserByIdentifierFailed(): void
    {
        $this->userFacade
            ->expects($this->once())
            ->method('findOneByUsername')
            ->willReturn(null);

        $this->expectException(UserNotFoundException::class);

        $this->appUserProvider->loadUserByIdentifier('identifier');
    }

    public function testRefreshUser(): void
    {
        $user = new User;
        $user->setUsername('username');

        $this->userFacade
            ->expects($this->once())
            ->method('findOneByUsername')
            ->willReturn($user);

        $result = $this->appUserProvider->refreshUser($user);

        $this->assertSame($user, $result);
    }

    public function testSupportsClass(): void
    {
        $this->assertTrue($this->appUserProvider->supportsClass(User::class));
    }
}
