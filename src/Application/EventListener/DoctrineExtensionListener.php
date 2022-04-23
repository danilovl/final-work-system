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

namespace App\Application\EventListener;

use App\Application\Service\UserService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;

class DoctrineExtensionListener implements EventSubscriberInterface
{
    public function __construct(
        private UserService $userService,
        private LoggableListener $loggableListener
    ) {
    }

    public function onKernelRequest(): void
    {
        $user = $this->userService->getUser();
        if ($user === null) {
            return;
        }

        $this->loggableListener->setUsername($user->getUsername());
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => 'onKernelRequest'
        ];
    }
}
