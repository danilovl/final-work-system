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

readonly class ConversationDeleteHandle
{
    public function __construct(
        private RequestService $requestService,
        private EntityManagerService $entityManagerService
    ) {}

    public function __invoke(Conversation $conversation): JsonResponse
    {
        $this->entityManagerService->remove($conversation);

        return $this->requestService->createAjaxJson(AjaxJsonTypeConstant::DELETE_SUCCESS);
    }
}
