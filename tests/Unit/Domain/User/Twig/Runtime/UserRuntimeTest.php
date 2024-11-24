<?php declare(strict_types=1);

/*
 *
 * This file is part of the FinalWorkSystem project.
 * (c) Vladimir Danilov
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace App\Tests\Unit\Domain\User\Twig\Runtime;

use App\Application\Service\S3ClientService;
use App\Domain\User\Entity\User;
use App\Domain\User\Service\UserService;
use App\Domain\User\Twig\Runtime\UserRuntime;
use Danilovl\HashidsBundle\Interfaces\HashidsServiceInterface;
use Danilovl\ParameterBundle\Interfaces\ParameterServiceInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\RouterInterface;

class UserRuntimeTest extends TestCase
{
    private UserRuntime $userRuntime;

    protected function setUp(): void
    {
        $user = new User;

        $userService = $this->createMock(UserService::class);
        $userService->expects($this->exactly(1))
            ->method('getUserOrNull')
            ->willReturn($user);

        $router = $this->createMock(RouterInterface::class);
        $hashidsService = $this->createMock(HashidsServiceInterface::class);
        $parameterService = $this->createMock(ParameterServiceInterface::class);
        $s3ClientService = $this->createMock(S3ClientService::class);

        $this->userRuntime = new UserRuntime(
            $userService,
            $router,
            $hashidsService,
            $parameterService,
            $s3ClientService
        );
    }

    public function testAppUser(): void
    {
        $this->assertInstanceOf(User::class, $this->userRuntime->appUser());
    }
}
