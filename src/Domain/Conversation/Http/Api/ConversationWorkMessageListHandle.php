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

namespace App\Domain\Conversation\Http\Api;

use App\Application\Constant\TabTypeConstant;
use App\Domain\User\Service\UserService;
use App\Domain\Work\Entity\Work;
use App\Domain\Work\Service\WorkDetailTabService;
use Danilovl\ObjectToArrayTransformBundle\Service\ObjectToArrayTransformService;
use Symfony\Component\HttpFoundation\{
    JsonResponse,
    Request
};

readonly class ConversationWorkMessageListHandle
{
    public function __construct(
        private UserService $userService,
        private WorkDetailTabService $workDetailTabService,
        private ObjectToArrayTransformService $objectToArrayTransformService
    ) {}

    public function __invoke(Request $request, Work $work): JsonResponse
    {
        $user = $this->userService->getUser();
        $pagination = $this->workDetailTabService->getTabPagination(
            $request,
            TabTypeConstant::TAB_MESSAGE->value,
            $work,
            $user
        );

        $messages = [];
        foreach ($pagination as $message) {
            $messages[] = $this->objectToArrayTransformService->transform('api_key_field', $message);
        }

        return new JsonResponse([
            'count' => $pagination->count(),
            'totalCount' => $pagination->getTotalItemCount(),
            'success' => true,
            'result' => $messages
        ]);
    }
}
