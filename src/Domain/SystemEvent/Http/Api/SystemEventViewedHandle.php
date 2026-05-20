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
use App\Infrastructure\Service\EntityManagerService;
use App\Domain\SystemEventRecipient\Entity\SystemEventRecipient;
use App\Domain\User\Service\UserService;
use Symfony\Component\HttpFoundation\{
    Response,
    JsonResponse
};

readonly class SystemEventViewedHandle
{
    public function __construct(
        private EntityManagerService $entityManagerService,
        private UserService $userService,
        private CacheEventDispatcher $cacheEventDispatcher
    ) {}

    public function __invoke(SystemEventRecipient $systemEventRecipient): JsonResponse
    {
        $systemEventRecipient->changeViewed();
        $this->entityManagerService->flush();

        $user = $this->userService->getUser();

        $this->cacheEventDispatcher->onClearCacheKey(
            sprintf(CacheKeyConstant::HOME_PAGE_USER_PAGINATOR->value, $user->getId())
        );

        return new JsonResponse(status: Response::HTTP_NO_CONTENT);
    }
}
