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

namespace App\Tests\Unit\Domain\User\EventListener;

use App\Domain\User\Entity\User;
use App\Domain\User\EventListener\DoctrineExtensionListener;
use App\Domain\User\Facade\UserFacade;
use App\Domain\User\Service\UserService;
use App\Infrastructure\Event\EventListener\LoggableListener;
use App\Infrastructure\Service\EntityManagerService;
use Doctrine\ORM\UnitOfWork;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

#[AllowMockObjectsWithoutExpectations]
class DoctrineExtensionListenerTest extends TestCase
{
    private MockObject&LoggableListener $loggableListener;

    private DoctrineExtensionListener $listener;

    protected function setUp(): void
    {
        $user = $this->createStub(User::class);
        $user
            ->method('getUsername')
            ->willReturn('username');

        $token = $this->createStub(TokenInterface::class);
        $token
            ->method('getUser')
            ->willReturn($user);

        $tokenStorage = $this->createStub(TokenStorageInterface::class);
        $tokenStorage
            ->method('getToken')
            ->willReturn($token);

        $unitOfWork = $this->createStub(UnitOfWork::class);
        $unitOfWork
            ->method('isInIdentityMap')
            ->willReturn(true);

        $entityManagerService = $this->createStub(EntityManagerService::class);
        $entityManagerService
            ->method('getUnitOfWork')
            ->willReturn($unitOfWork);

        $userFacade = $this->createStub(UserFacade::class);

        $userService = new UserService($tokenStorage, $entityManagerService, $userFacade);
        $this->loggableListener = $this->createMock(LoggableListener::class);

        $this->listener = new DoctrineExtensionListener($userService, $this->loggableListener);
    }

    public function testOnKernelRequest(): void
    {
        $this->loggableListener
            ->expects($this->once())
            ->method('setUsername')
            ->with('username');

        $this->listener->onKernelRequest();
    }

    public function testOnKernelRequestNoUser(): void
    {
        $tokenStorage = $this->createStub(TokenStorageInterface::class);
        $tokenStorage
            ->method('getToken')
            ->willReturn(null);

        $entityManagerService = $this->createStub(EntityManagerService::class);
        $userFacade = $this->createStub(UserFacade::class);

        $userService = new UserService($tokenStorage, $entityManagerService, $userFacade);

        $this->loggableListener = $this->createMock(LoggableListener::class);
        $this->loggableListener
            ->expects($this->never())
            ->method('setUsername');

        $listener = new DoctrineExtensionListener($userService, $this->loggableListener);

        $listener->onKernelRequest();
    }

    public function testGetSubscribedEvents(): void
    {
        $subscribedEvents = $this->listener::getSubscribedEvents();

        $this->assertArrayHasKey(KernelEvents::REQUEST, $subscribedEvents);
        $this->assertEquals('onKernelRequest', $subscribedEvents[KernelEvents::REQUEST]);
    }
}
