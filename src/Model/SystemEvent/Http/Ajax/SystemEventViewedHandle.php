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

namespace App\Model\SystemEvent\Http\Ajax;

use App\EventDispatcher\CacheEventDispatcherService;
use App\Constant\{
    CacheKeyConstant,
    AjaxJsonTypeConstant
};
use App\Model\SystemEventRecipient\Entity\SystemEventRecipient;
use App\Service\{
    UserService,
    RequestService,
    EntityManagerService
};
use Symfony\Component\HttpFoundation\JsonResponse;

class SystemEventViewedHandle
{
    public function __construct(
        private EntityManagerService $entityManagerService,
        private RequestService $requestService,
        private UserService $userService,
        private CacheEventDispatcherService $cacheEventDispatcherService
    ) {
    }

    public function handle(SystemEventRecipient $systemEventRecipient): JsonResponse
    {
        $systemEventRecipient->changeViewed();
        $this->entityManagerService->flush($systemEventRecipient);

        $user = $this->userService->getUser();

        $this->cacheEventDispatcherService->onClearCacheKey(
            sprintf(CacheKeyConstant::HOME_PAGE_USER_PAGINATOR, $user->getId())
        );

        return $this->requestService->createAjaxJson(AjaxJsonTypeConstant::SAVE_SUCCESS);
    }
}
