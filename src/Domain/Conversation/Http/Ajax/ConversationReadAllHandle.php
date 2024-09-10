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
    RequestService,
    EntityManagerService
};
use App\Domain\Conversation\Facade\ConversationMessageFacade;
use App\Domain\ConversationMessageStatus\Facade\ConversationMessageStatusFacade;
use App\Domain\ConversationMessageStatusType\Constant\ConversationMessageStatusTypeConstant;
use App\Domain\ConversationMessageStatusType\Entity\ConversationMessageStatusType;
use App\Domain\User\Service\UserService;
use Symfony\Component\HttpFoundation\JsonResponse;

readonly class ConversationReadAllHandle
{
    public function __construct(
        private RequestService $requestService,
        private UserService $userService,
        private EntityManagerService $entityManagerService,
        private ConversationMessageFacade $conversationMessageFacade,
        private ConversationMessageStatusFacade $conversationMessageStatusFacade
    ) {}

    public function __invoke(): JsonResponse
    {
        $user = $this->userService->getUser();

        $isUnreadExist = $this->conversationMessageFacade
            ->isUnreadMessagesByRecipient($user);

        if ($isUnreadExist) {
            /** @var ConversationMessageStatusType $conversationMessageStatusType */
            $conversationMessageStatusType = $this->entityManagerService->getReference(
                ConversationMessageStatusType::class,
                ConversationMessageStatusTypeConstant::READ->value
            );

            $this->conversationMessageStatusFacade->updateAllToStatus(
                $user,
                $conversationMessageStatusType
            );
        }

        return $this->requestService->createAjaxJson(AjaxJsonTypeConstant::SAVE_SUCCESS);
    }
}
