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

namespace App\Model\Conversation\Http\Ajax;

use App\Constant\AjaxJsonTypeConstant;
use App\Entity\ConversationMessage;
use App\Model\Conversation\Facade\ConversationMessageFacade;
use App\Service\{
    UserService,
    RequestService
};
use Symfony\Component\HttpFoundation\JsonResponse;

class ConversationChangeReadMessageStatusHandle
{
    public function __construct(
        private RequestService $requestService,
        private UserService $userService,
        private ConversationMessageFacade $conversationMessageFacade
    ) {
    }

    public function handle(ConversationMessage $conversationMessage): JsonResponse
    {
        $this->conversationMessageFacade->changeReadMessageStatus(
            $this->userService->getUser(),
            $conversationMessage
        );

        return $this->requestService->createAjaxJson(AjaxJsonTypeConstant::SAVE_SUCCESS);
    }
}
