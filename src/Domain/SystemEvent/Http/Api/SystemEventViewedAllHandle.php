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

namespace App\Domain\SystemEvent\Http\Api;

use App\Application\Constant\CacheKeyConstant;
use App\Application\EventDispatcher\CacheEventDispatcher;
use Symfony\Component\HttpFoundation\Response;
use App\Domain\SystemEvent\Facade\{
    SystemEventFacade,
    SystemEventRecipientFacade
};
use App\Domain\User\Service\UserService;
use Symfony\Component\HttpFoundation\JsonResponse;

readonly class SystemEventViewedAllHandle
{
    public function __construct(
        private UserService $userService,
        private SystemEventFacade $systemEventFacade,
        private SystemEventRecipientFacade $systemEventRecipientFacade,
        private CacheEventDispatcher $cacheEventDispatcher
    ) {}

    public function __invoke(): JsonResponse
    {
        $user = $this->userService->getUser();
        $isUnreadExist = $this->systemEventFacade
            ->isUnreadSystemEventsByRecipient($user);

        if ($isUnreadExist === true) {
            $this->systemEventRecipientFacade->updateViewedAll($user);

            $this->cacheEventDispatcher->onClearCacheKey(
                sprintf(CacheKeyConstant::HOME_PAGE_USER_PAGINATOR->value, $user->getId())
            );
        }

        return new JsonResponse(status: Response::HTTP_NO_CONTENT);
    }
}
