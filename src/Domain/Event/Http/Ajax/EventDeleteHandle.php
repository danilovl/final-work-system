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

namespace App\Domain\Event\Http\Ajax;

use App\Application\Constant\AjaxJsonTypeConstant;
use App\Application\Service\{
    RequestService,
    EntityManagerService
};
use App\Domain\Event\Entity\Event;
use Symfony\Component\HttpFoundation\JsonResponse;

readonly class EventDeleteHandle
{
    public function __construct(
        private RequestService $requestService,
        private EntityManagerService $entityManagerService
    ) {}

    public function handle(Event $event): JsonResponse
    {
        $this->entityManagerService->remove($event);

        return $this->requestService->createAjaxJson(AjaxJsonTypeConstant::DELETE_SUCCESS);
    }
}
