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
use App\Application\Service\{
    UserService,
    RequestService,
    EntityManagerService
};
use App\Domain\SystemEventRecipient\Entity\SystemEventRecipient;
use Symfony\Component\HttpFoundation\JsonResponse;

class SystemEventViewedHandle
{
    public function __construct(
        private readonly EntityManagerService $entityManagerService,
        private readonly RequestService $requestService,
        private readonly UserService $userService,
        private readonly CacheEventDispatcherService $cacheEventDispatcherService
    ) {}

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
