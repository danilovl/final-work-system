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

namespace App\Tests\Unit\Application\EventListener;

use App\Application\Service\EntityManagerService;
use App\Domain\User\Facade\UserFacade;
use Doctrine\ORM\UnitOfWork;
use App\Application\EventListener\{
    LoggableListener,
    DoctrineExtensionListener
};
use App\Domain\User\Service\UserService;
use App\Domain\User\Entity\User;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class DoctrineExtensionListenerTest extends TestCase
{
    private TokenInterface $token;
    private TokenStorageInterface $tokenStorage;
    private LoggableListener $loggableListener;
    private UserService $userService;
    private DoctrineExtensionListener $listener;

    protected function setUp(): void
    {
        $user = $this->createMock(User::class);
        $user->expects($this->any())
            ->method('getUsername')
            ->willReturn('username');

        $this->token = $this->createMock(TokenInterface::class);
        $this->token->expects($this->any())
            ->method('getUser')
            ->willReturn($user);

        $this->tokenStorage = $this->createMock(TokenStorageInterface::class);
        $this->tokenStorage->expects($this->any())
            ->method('getToken')
            ->willReturn($this->token);

        $unitOfWork = $this->createMock(UnitOfWork::class);
        $unitOfWork->expects($this->any())
            ->method('isInIdentityMap')
            ->willReturn(true);

        $entityManagerService = $this->createMock(EntityManagerService::class);
        $entityManagerService->expects($this->any())
            ->method('getUnitOfWork')
            ->willReturn($unitOfWork);

        $userFacade = $this->createMock(UserFacade::class);

        $this->userService = new UserService($this->tokenStorage, $entityManagerService, $userFacade);
        $this->loggableListener = $this->createMock(LoggableListener::class);
        $this->loggableListener->expects($this->any())
            ->method('setUsername')
            ->with($user->getUsername());

        $this->listener = new DoctrineExtensionListener($this->userService, $this->loggableListener);
    }

    public function testOnKernelRequest(): void
    {
        $this->listener->onKernelRequest();

        $this->assertTrue(true);
    }

    public function testOnKernelRequestNoUser(): void
    {
        $tokenStorage = $this->createMock(TokenStorageInterface::class);
        $tokenStorage->expects($this->once())
            ->method('getToken')
            ->willReturn(null);

        $entityManagerService = $this->createMock(EntityManagerService::class);
        $userFacade = $this->createMock(UserFacade::class);

        $userService = new UserService($tokenStorage, $entityManagerService, $userFacade);

        $this->loggableListener
            ->expects($this->never())
            ->method('setUsername');

        $listener = new DoctrineExtensionListener($userService, $this->loggableListener);

        $listener->onKernelRequest();

        $this->assertTrue(true);
    }

    public function testGetSubscribedEvents(): void
    {
        $subscribedEvents = $this->listener::getSubscribedEvents();

        $this->assertArrayHasKey(KernelEvents::REQUEST, $subscribedEvents);
        $this->assertEquals('onKernelRequest', $subscribedEvents[KernelEvents::REQUEST]);
    }
}
