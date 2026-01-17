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
use App\Application\Interfaces\Bus\CommandBusInterface;
use App\Domain\Conversation\Bus\Command\UpdateAllToStatus\UpdateAllToStatusCommand;
use App\Infrastructure\Service\{
    RequestService,
    EntityManagerService
};
use App\Domain\Conversation\Facade\ConversationMessageFacade;
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
        private CommandBusInterface $commandBus
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

            $command = UpdateAllToStatusCommand::create($user, $conversationMessageStatusType);
            $this->commandBus->dispatch($command);
        }

        return $this->requestService->createAjaxJson(AjaxJsonTypeConstant::SAVE_SUCCESS);
    }
}
