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
use App\Domain\Conversation\Service\ConversationStatusService;
use App\Domain\ConversationMessage\Factory\WorkConversationMessageDTOFactory;
use Symfony\Component\HttpFoundation\{
    JsonResponse,
    Request
};

readonly class ConversationWorkMessageListHandle
{
    public function __construct(
        private UserService $userService,
        private WorkDetailTabService $workDetailTabService,
        private WorkConversationMessageDTOFactory $workConversationMessageDTOFactory,
        private ConversationStatusService $conversationStatusService
    ) {}

    public function __invoke(Request $request, Work $work): JsonResponse
    {
        $user = $this->userService->getUser();
        $pagination = $this->workDetailTabService->getTabPagination(
            request: $request,
            tab: TabTypeConstant::TAB_MESSAGE->value,
            work: $work,
            user: $user,
            isApi: true
        );

        $messages = [];
        foreach ($pagination as $message) {
            $isRead = $this->conversationStatusService->isConversationMessageRead($message, $user);

            $messages[] = $this->workConversationMessageDTOFactory->create(
                message: $message,
                isRead: $isRead
            );
        }

        return new JsonResponse([
            'count' => $pagination->count(),
            'totalCount' => $pagination->getTotalItemCount(),
            'success' => true,
            'result' => $messages
        ]);
    }
}
