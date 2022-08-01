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

use App\Application\Constant\{
    ConversationMessageStatusTypeConstant
};
use App\Application\Constant\AjaxJsonTypeConstant;
use App\Application\Service\{
    RequestService
};
use App\Application\Service\EntityManagerService;
use App\Application\Service\UserService;
use App\Domain\Conversation\Facade\ConversationMessageFacade;
use App\Domain\ConversationMessageStatus\Facade\ConversationMessageStatusFacade;
use App\Domain\ConversationMessageStatusType\Entity\ConversationMessageStatusType;
use Symfony\Component\HttpFoundation\JsonResponse;

class ConversationReadAllHandle
{
    public function __construct(
        private readonly RequestService $requestService,
        private readonly UserService $userService,
        private readonly EntityManagerService $entityManagerService,
        private readonly ConversationMessageFacade $conversationMessageFacade,
        private readonly ConversationMessageStatusFacade $conversationMessageStatusFacade
    ) {}

    public function handle(): JsonResponse
    {
        $user = $this->userService->getUser();

        $isUnreadExist = $this->conversationMessageFacade
            ->isUnreadMessagesByRecipient($user);

        if ($isUnreadExist) {
            /** @var ConversationMessageStatusType $conversationMessageStatusType */
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
