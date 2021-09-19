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
use App\Model\SystemEvent\Facade\{
    SystemEventFacade,
    SystemEventRecipientFacade
};
use App\Constant\{
    CacheKeyConstant,
    AjaxJsonTypeConstant
};
use App\Service\{
    UserService,
    RequestService
};
use Symfony\Component\HttpFoundation\JsonResponse;

class SystemEventViewedAllHandle
{
    public function __construct(
        private RequestService $requestService,
        private UserService $userService,
        private SystemEventFacade $systemEventFacade,
        private SystemEventRecipientFacade $systemEventRecipientFacade,
        private CacheEventDispatcherService $cacheEventDispatcherService
    ) {
    }

    public function handle(): JsonResponse
    {
        $user = $this->userService->getUser();
        $isUnreadExist = $this->systemEventFacade
            ->isUnreadSystemEventsByRecipient($user);

        if ($isUnreadExist === true) {
            $this->systemEventRecipientFacade->updateViewedAll($user);

            $this->cacheEventDispatcherService->onClearCacheKey(
                sprintf(CacheKeyConstant::HOME_PAGE_USER_PAGINATOR, $user->getId())
            );
        }

        return $this->requestService->createAjaxJson(AjaxJsonTypeConstant::SAVE_SUCCESS);
    }
}
