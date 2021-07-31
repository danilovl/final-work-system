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

namespace App\EventListener;

use App\Service\{SeoPageService, EntityManagerService, User\UserService};
use DateTime;
use Symfony\Component\HttpKernel\Event\RequestEvent;

class RequestListener
{
    public function __construct(
        private UserService $userService,
        private EntityManagerService $entityManagerService,
        private SeoPageService $seoPageService
    ) {
    }

    public function onKernelRequest(RequestEvent $requestEvent): void
    {
        if (!$requestEvent->isMainRequest()) {
            return;
        }

        $this->userLastRequestedAt();
        $this->defaultRouteSeoPage($requestEvent);
    }

    private function userLastRequestedAt(): void
    {
        $user = $this->userService->getUser();
        if ($user === null) {
            return;
        }

        $user->setLastRequestedAt(new DateTime);
        $this->entityManagerService->flush($user);
    }

    private function defaultRouteSeoPage(RequestEvent $requestEvent): void
    {
        $seo = $requestEvent->getRequest()->attributes->get('_seo');
        $this->seoPageService->setTitle($seo['title'] ?? null);
    }
}
