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
use App\Application\Interfaces\Bus\{
    QueryBusInterface,
    QueryInterface
};
use App\Domain\User\Bus\Query\UserByUsername\{
    GetUserByUsernameQuery,
    GetUserByUsernameQueryResult
};
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
        $queryBus = $this->createStub(QueryBusInterface::class);
        $this->entityManagerService = $this->createStub(EntityManagerService::class);

        $queryBus
            ->method('handle')
            ->willReturnCallback(static function (QueryInterface $query): GetUserByUsernameQueryResult {
                $isKnownUser = $query instanceof GetUserByUsernameQuery
                    && in_array($query->username, ['identifier', 'username'], true);
                $user = $isKnownUser ? new User : null;

                return new GetUserByUsernameQueryResult($user);
            });

        $this->appUserProvider = new AppUserProvider(
            $queryBus,
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
