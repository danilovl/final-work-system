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

namespace App\Model\EventAddress\Http;

use App\Service\{PaginatorService,
    UserService,
    TwigRenderService
};
use Symfony\Component\HttpFoundation\{
    Request,
    Response
};

class EventAddressListHandle
{
    public function __construct(
        private UserService $userService,
        private TwigRenderService $twigRenderService,
        private PaginatorService $paginatorService
    ) {
    }

    public function handle(Request $request): Response
    {
        $user = $this->userService->getUser();
        $eventAddresses = $this->paginatorService->createPaginationRequest(
            $request,
            $user->getEventAddressOwner()
        );

        return $this->twigRenderService->render('event_address/list.html.twig', [
            'eventAddresses' => $eventAddresses
        ]);
    }
}
