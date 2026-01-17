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

use Override;
use App\Infrastructure\Service\EntityManagerService;
use App\Domain\User\Service\UserService;
use Danilovl\AsyncBundle\Service\AsyncService;
use DateTime;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

readonly class RequestListener implements EventSubscriberInterface
{
    public function __construct(
        private UserService $userService,
        private EntityManagerService $entityManagerService,
        private AsyncService $asyncService
    ) {}

    #[Override]
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => 'onKernelRequest'
        ];
    }

    public function onKernelRequest(RequestEvent $requestEvent): void
    {
        if (!$requestEvent->isMainRequest()) {
            return;
        }

        $this->asyncService->add(function (): void {
            $this->userLastRequestedAt();
        });
    }

    private function userLastRequestedAt(): void
    {
        $user = $this->userService->getUserOrNull();
        if ($user === null) {
            return;
        }

        $user->setLastRequestedAt(new DateTime);
        $this->entityManagerService->flush();
    }
}
