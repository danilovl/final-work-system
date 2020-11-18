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

use App\Services\{
    UserService,
    EntityManagerService
};
use DateTime;
use Symfony\Component\HttpKernel\Event\RequestEvent;

class RequestListener
{
    private UserService $userService;
    private EntityManagerService $entityManagerService;

    public function __construct(
        UserService $userService,
        EntityManagerService $entityManagerService
    ) {
        $this->userService = $userService;
        $this->entityManagerService = $entityManagerService;
    }

    public function onKernelRequest(RequestEvent $requestEvent): void
    {
        if (!$requestEvent->isMasterRequest()) {
            return;
        }

        $user = $this->userService->getUser();
        if ($user === null) {
            return;
        }

        $user->setLastRequestedAt(new DateTime);
        $this->entityManagerService->flush($user);
    }
}
