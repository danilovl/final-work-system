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

namespace App\Domain\User\EventListener;

use App\Domain\User\Service\UserService;
use App\Infrastructure\Event\EventListener\LoggableListener;
use Override;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;

readonly class DoctrineExtensionListener implements EventSubscriberInterface
{
    public function __construct(
        private UserService $userService,
        private LoggableListener $loggableListener
    ) {}

    #[Override]
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => 'onKernelRequest'
        ];
    }

    public function onKernelRequest(): void
    {
        $user = $this->userService->getUserOrNull();
        if ($user === null) {
            return;
        }

        $this->loggableListener->setUsername($user->getUsername());
    }
}
