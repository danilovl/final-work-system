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

namespace App\Domain\Conversation\Http\Ajax;

use App\Application\Constant\AjaxJsonTypeConstant;
use App\Application\Service\{
    EntityManagerService
};
use App\Application\Service\RequestService;
use App\Domain\Conversation\Entity\Conversation;
use Symfony\Component\HttpFoundation\JsonResponse;

class ConversationDeleteHandle
{
    public function __construct(
        private readonly RequestService $requestService,
        private readonly EntityManagerService $entityManagerService
    ) {}

    public function handle(Conversation $conversation): JsonResponse
    {
        $this->entityManagerService->remove($conversation);

        return $this->requestService->createAjaxJson(AjaxJsonTypeConstant::DELETE_SUCCESS);
    }
}
