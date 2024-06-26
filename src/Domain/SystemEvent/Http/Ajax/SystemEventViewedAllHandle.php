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

namespace App\Domain\SystemEvent\Http\Ajax;

use App\Application\Constant\{
    CacheKeyConstant,
    AjaxJsonTypeConstant
};
use App\Application\EventDispatcher\CacheEventDispatcherService;
use App\Application\Service\RequestService;
use App\Domain\SystemEvent\Facade\{
    SystemEventFacade,
    SystemEventRecipientFacade
};
use App\Domain\User\Service\UserService;
use Symfony\Component\HttpFoundation\JsonResponse;

readonly class SystemEventViewedAllHandle
{
    public function __construct(
        private RequestService $requestService,
        private UserService $userService,
        private SystemEventFacade $systemEventFacade,
        private SystemEventRecipientFacade $systemEventRecipientFacade,
        private CacheEventDispatcherService $cacheEventDispatcherService
    ) {}

    public function __invoke(): JsonResponse
    {
        $user = $this->userService->getUser();
        $isUnreadExist = $this->systemEventFacade
            ->isUnreadSystemEventsByRecipient($user);

        if ($isUnreadExist === true) {
            $this->systemEventRecipientFacade->updateViewedAll($user);

            $this->cacheEventDispatcherService->onClearCacheKey(
                sprintf(CacheKeyConstant::HOME_PAGE_USER_PAGINATOR->value, $user->getId())
            );
        }

        return $this->requestService->createAjaxJson(AjaxJsonTypeConstant::SAVE_SUCCESS);
    }
}
