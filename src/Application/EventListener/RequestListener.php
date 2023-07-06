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

namespace App\Application\EventListener;

use Danilovl\AsyncBundle\Service\AsyncService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use App\Application\Service\{
    UserService,
    SeoPageService,
    EntityManagerService
};
use DateTime;
use Symfony\Component\HttpKernel\Event\RequestEvent;

readonly class RequestListener implements EventSubscriberInterface
{
    public function __construct(
        private UserService $userService,
        private EntityManagerService $entityManagerService,
        private SeoPageService $seoPageService,
        private AsyncService $asyncService
    ) {}

    public function onKernelRequest(RequestEvent $requestEvent): void
    {
        if (!$requestEvent->isMainRequest()) {
            return;
        }

        $this->defaultRouteSeoPage($requestEvent);

        $this->asyncService->add(function (): void {
            $this->userLastRequestedAt();
        });
    }

    private function userLastRequestedAt(): void
    {
        $user = $this->userService->getUser();
        if ($user === null) {
            return;
        }

        $user->setLastRequestedAt(new DateTime);
        $this->entityManagerService->flush();
    }

    private function defaultRouteSeoPage(RequestEvent $requestEvent): void
    {
        $seo = $requestEvent->getRequest()->attributes->get('_seo');
        $this->seoPageService->setTitle($seo['title'] ?? null);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => 'onKernelRequest'
        ];
    }
}
