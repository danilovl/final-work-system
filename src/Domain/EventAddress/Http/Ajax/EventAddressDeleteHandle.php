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

namespace App\Domain\EventAddress\Http\Ajax;

use App\Application\Constant\AjaxJsonTypeConstant;
use App\Application\Service\{
    RequestService,
    EntityManagerService
};
use App\Domain\EventAddress\Entity\EventAddress;
use Symfony\Component\HttpFoundation\JsonResponse;

class EventAddressDeleteHandle
{
    public function __construct(
        private readonly RequestService $requestService,
        private readonly EntityManagerService $entityManagerService
    ) {}

    public function handle(EventAddress $eventAddress): JsonResponse
    {
        $this->entityManagerService->remove($eventAddress);

        return $this->requestService->createAjaxJson(AjaxJsonTypeConstant::DELETE_SUCCESS);
    }
}
