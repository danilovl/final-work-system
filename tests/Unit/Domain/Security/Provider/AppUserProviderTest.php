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

namespace App\Tests\Unit\Domain\Security\Provider;

use App\Domain\Security\Provider\AppUserProvider;
use App\Domain\User\Entity\User;
use App\Domain\User\Facade\UserFacade;
use App\Infrastructure\Service\EntityManagerService;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;

class AppUserProviderTest extends TestCase
{
    private Stub&EntityManagerService $entityManagerService;

    private AppUserProvider $appUserProvider;

    protected function setUp(): void
    {
        $userFacade = $this->createStub(UserFacade::class);
        $this->entityManagerService = $this->createStub(EntityManagerService::class);

        $userFacade
            ->method('findByUsername')
            ->willReturnCallback(static function (string $username): ?User {
                return match ($username) {
                    'identifier', 'username' => new User,
                    default => null
                };
            });

        $this->appUserProvider = new AppUserProvider(
            $userFacade,
            $this->entityManagerService
        );
    }

    public function testUpgradePassword(): void
    {
        $this->entityManagerService
            ->method('flush');

        $this->appUserProvider->upgradePassword(new User, 'newHashedPassword');
        $this->expectNotToPerformAssertions();
    }

    public function testLoadUserByIdentifierSuccess(): void
    {
        $result = $this->appUserProvider->loadUserByIdentifier('identifier');

        $this->assertInstanceOf(User::class, $result);
    }

    public function testLoadUserByIdentifierFailed(): void
    {
        $this->expectException(UserNotFoundException::class);

        $this->appUserProvider->loadUserByIdentifier('invalid');
    }

    public function testRefreshUser(): void
    {
        $user = new User;
        $user->setUsername('username');

        $result = $this->appUserProvider->refreshUser($user);

        $this->assertInstanceOf(User::class, $result);
    }

    public function testSupportsClass(): void
    {
        $this->assertTrue($this->appUserProvider->supportsClass(User::class));
    }
}
