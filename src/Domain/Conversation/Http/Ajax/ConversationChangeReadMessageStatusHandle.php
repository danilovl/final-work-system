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
    UserService,
    RequestService
};
use App\Domain\Conversation\Facade\ConversationMessageFacade;
use App\Domain\ConversationMessage\Entity\ConversationMessage;
use Symfony\Component\HttpFoundation\JsonResponse;

readonly class ConversationChangeReadMessageStatusHandle
{
    public function __construct(
        private RequestService $requestService,
        private UserService $userService,
        private ConversationMessageFacade $conversationMessageFacade
    ) {}

    public function handle(ConversationMessage $conversationMessage): JsonResponse
    {
        $this->conversationMessageFacade->changeReadMessageStatus(
            $this->userService->getUser(),
            $conversationMessage
        );

        return $this->requestService->createAjaxJson(AjaxJsonTypeConstant::SAVE_SUCCESS);
    }
}
