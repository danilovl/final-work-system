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

use App\Application\Interfaces\Bus\{CommandBusInterface, QueryBusInterface};
use App\Domain\ConversationMessage\Bus\Query\HasUnreadMessagesByRecipient\{GetHasUnreadMessagesByRecipientQuery, GetHasUnreadMessagesByRecipientQueryResult};
use App\Domain\ConversationMessageStatus\Bus\Command\UpdateAllToStatus\UpdateAllToStatusCommand;
use App\Infrastructure\Service\EntityManagerService;
use App\Domain\ConversationMessageStatusType\Constant\ConversationMessageStatusTypeConstant;
use App\Domain\ConversationMessageStatusType\Entity\ConversationMessageStatusType;
use App\Domain\User\Service\UserService;
use Symfony\Component\HttpFoundation\{
    Response,
    JsonResponse
};

readonly class ConversationChangeAllMessageToReadHandle
{
    public function __construct(
        private UserService $userService,
        private EntityManagerService $entityManagerService,
        private QueryBusInterface $queryBus,
        private CommandBusInterface $commandBus
    ) {}

    public function __invoke(): JsonResponse
    {
        $user = $this->userService->getUser();
        $getHasUnreadMessagesByRecipientQuery = GetHasUnreadMessagesByRecipientQuery::create($user);
        /** @var GetHasUnreadMessagesByRecipientQueryResult $getHasUnreadMessagesByRecipientQueryResult */
        $getHasUnreadMessagesByRecipientQueryResult = $this->queryBus->handle($getHasUnreadMessagesByRecipientQuery);
        $isUnreadExist = $getHasUnreadMessagesByRecipientQueryResult->hasUnread;

        if ($isUnreadExist) {
            /** @var ConversationMessageStatusType $conversationMessageStatusType */
            $conversationMessageStatusType = $this->entityManagerService->getReference(
                ConversationMessageStatusType::class,
                ConversationMessageStatusTypeConstant::READ->value
            );

            $command = UpdateAllToStatusCommand::create($user, $conversationMessageStatusType);
            $this->commandBus->dispatch($command);
        }

        return new JsonResponse(status: Response::HTTP_OK);
    }
}
