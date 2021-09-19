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

use App\Constant\{
    AjaxJsonTypeConstant,
    ConversationMessageStatusTypeConstant
};
use App\Entity\ConversationMessageStatusType;
use App\Model\ConversationMessageStatus\Facade\ConversationMessageStatusFacade;
use App\Model\Conversation\Facade\ConversationMessageFacade;
use App\Service\{
    UserService,
    RequestService,
    EntityManagerService
};
use Symfony\Component\HttpFoundation\JsonResponse;

class ConversationReadAllHandle
{
    public function __construct(
        private RequestService $requestService,
        private UserService $userService,
        private EntityManagerService $entityManagerService,
        private ConversationMessageFacade $conversationMessageFacade,
        private ConversationMessageStatusFacade $conversationMessageStatusFacade
    ) {
    }

    public function handle(): JsonResponse
    {
        $user = $this->userService->getUser();

        $isUnreadExist = $this->conversationMessageFacade
            ->isUnreadMessagesByRecipient($user);

        if ($isUnreadExist) {
            $conversationMessageStatusType = $this->entityManagerService->getReference(
                ConversationMessageStatusType::class,
                ConversationMessageStatusTypeConstant::READ
            );

            $this->conversationMessageStatusFacade->updateAllToStatus(
                $user,
                $conversationMessageStatusType
            );
        }

        return $this->requestService->createAjaxJson(AjaxJsonTypeConstant::SAVE_SUCCESS);
    }
}
