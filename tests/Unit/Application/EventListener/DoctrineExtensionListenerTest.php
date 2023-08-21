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
    private DoctrineExtensionListener $listener;

    protected function setUp(): void
    {
        $user = $this->createMock(User::class);
        $user->expects($this->any())
            ->method('getUsername')
            ->willReturn('username');

        $token = $this->createMock(TokenInterface::class);
        $token->expects($this->any())
            ->method('getUser')
            ->willReturn($user);

        $tokenStorage = $this->createMock(TokenStorageInterface::class);
        $tokenStorage->expects($this->any())
            ->method('getToken')
            ->willReturn($token);

        $userService = new UserService($tokenStorage);
        $loggableListener = $this->createMock(LoggableListener::class);

        $loggableListener->expects($this->any())
            ->method('setUsername')
            ->with($user->getUsername());

        $this->listener = new DoctrineExtensionListener($userService, $loggableListener);
    }

    public function testOnKernelRequest(): void
    {
        $this->listener->onKernelRequest();

        $this->assertTrue(true);
    }

    public function testGetSubscribedEvents(): void
    {
        $subscribedEvents = $this->listener::getSubscribedEvents();

        $this->assertArrayHasKey(KernelEvents::REQUEST, $subscribedEvents);
        $this->assertEquals('onKernelRequest', $subscribedEvents[KernelEvents::REQUEST]);
    }
}
