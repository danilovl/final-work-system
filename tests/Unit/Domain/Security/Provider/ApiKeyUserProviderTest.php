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

use App\Domain\ApiUser\Entity\ApiUser;
use App\Domain\ApiUser\Facade\ApiUserFacade;
use App\Domain\Security\Provider\ApiKeyUserProvider;
use App\Domain\User\Entity\User;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Exception\{
    UnsupportedUserException,
    UserNotFoundException
};

class ApiKeyUserProviderTest extends TestCase
{
    private MockObject&ApiUserFacade $apiUserFacade;

    private ApiKeyUserProvider $apiKeyUserProvider;

    protected function setUp(): void
    {
        $this->apiUserFacade = $this->createMock(ApiUserFacade::class);

        $this->apiKeyUserProvider = new ApiKeyUserProvider($this->apiUserFacade);
    }

    public function testLoadUserByIdentifierSuccess(): void
    {
        $user = new ApiUser;

        $this->apiUserFacade
            ->expects($this->once())
            ->method('findByApiKey')
            ->willReturn($user);

        $result = $this->apiKeyUserProvider->loadUserByIdentifier('identifier');

        $this->assertSame($user, $result);
    }

    public function testLoadUserByIdentifierFailed(): void
    {
        $this->apiUserFacade
            ->expects($this->once())
            ->method('findByApiKey')
            ->willReturn(null);

        $this->expectException(UserNotFoundException::class);

        $this->apiKeyUserProvider->loadUserByIdentifier('identifier');
    }

    public function testRefreshUser(): void
    {
        $this->expectException(UnsupportedUserException::class);

        $this->apiKeyUserProvider->refreshUser(new User);
    }

    public function testSupportsClass(): void
    {
        $this->assertTrue($this->apiKeyUserProvider->supportsClass(ApiUser::class));
    }
}
