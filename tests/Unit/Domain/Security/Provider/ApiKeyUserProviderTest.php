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
use App\Application\Interfaces\Bus\{
    QueryBusInterface,
    QueryInterface
};
use App\Domain\ApiUser\Bus\Query\ApiUserByKey\{
    GetApiUserByKeyQuery,
    GetApiUserByKeyQueryResult
};
use App\Domain\Security\Provider\ApiKeyUserProvider;
use App\Domain\User\Entity\User;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Exception\{
    UnsupportedUserException,
    UserNotFoundException
};

class ApiKeyUserProviderTest extends TestCase
{
    private QueryBusInterface $queryBus;

    private ApiKeyUserProvider $apiKeyUserProvider;

    protected function setUp(): void
    {
        $queryBus = $this->createStub(QueryBusInterface::class);

        $queryBus
            ->method('handle')
            ->willReturnCallback(static function (QueryInterface $query): GetApiUserByKeyQueryResult {
                $isKnownKey = $query instanceof GetApiUserByKeyQuery && $query->apiKey === 'identifier';
                $apiUser = $isKnownKey ? new ApiUser : null;

                return new GetApiUserByKeyQueryResult($apiUser);
            });

        $this->queryBus = $queryBus;
        $this->apiKeyUserProvider = new ApiKeyUserProvider($this->queryBus);
    }

    public function testLoadUserByIdentifierSuccess(): void
    {
        $result = $this->apiKeyUserProvider->loadUserByIdentifier('identifier');

        $this->assertInstanceOf(ApiUser::class, $result);
    }

    public function testLoadUserByIdentifierFailed(): void
    {
        $this->expectException(UserNotFoundException::class);

        $this->apiKeyUserProvider->loadUserByIdentifier('invalid');
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
