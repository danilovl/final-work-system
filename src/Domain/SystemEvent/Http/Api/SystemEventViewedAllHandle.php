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
use App\Infrastructure\Event\EventDispatcher\CacheEventDispatcher;
use App\Domain\SystemEvent\Facade\SystemEventFacade;
use App\Infrastructure\Interfaces\Bus\CommandBusInterface;
use App\Domain\SystemEventRecipient\Bus\Command\ViewedAllSystemEventRecipient\ViewedAllSystemEventRecipientCommand;
use App\Domain\User\Service\UserService;
use Symfony\Component\HttpFoundation\{JsonResponse, Response};

readonly class SystemEventViewedAllHandle
{
    public function __construct(
        private UserService $userService,
        private SystemEventFacade $systemEventFacade,
        private CommandBusInterface $commandBus,
        private CacheEventDispatcher $cacheEventDispatcher
    ) {}

    public function __invoke(): JsonResponse
    {
        $user = $this->userService->getUser();
        $isUnreadExist = $this->systemEventFacade
            ->isUnreadSystemEventsByRecipient($user);

        if ($isUnreadExist === true) {
            $viewedAllCommand = ViewedAllSystemEventRecipientCommand::create($user);
            $this->commandBus->dispatch($viewedAllCommand);

            $cacheKey = sprintf(CacheKeyConstant::HOME_PAGE_USER_PAGINATOR->value, $user->getId());

            $this->cacheEventDispatcher->onClearCacheKey($cacheKey);
        }

        return new JsonResponse(status: Response::HTTP_NO_CONTENT);
    }
}
