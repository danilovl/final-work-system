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

use App\Application\Interfaces\Bus\CommandBusInterface;
use App\Domain\Conversation\Bus\Command\CreateConversationMessage\CreateConversationMessageCommand;
use App\Domain\Conversation\DTO\Api\Input\ConversationMessageInput;
use App\Domain\Conversation\Entity\Conversation;
use App\Domain\ConversationMessage\Model\ConversationMessageModel;
use App\Domain\User\Service\UserService;
use Symfony\Component\HttpFoundation\{
    Response,
    JsonResponse
};

readonly class ConversationCreateMessageHandle
{
    public function __construct(
        private UserService $userService,
        private CommandBusInterface $commandBus
    ) {}

    public function __invoke(Conversation $conversation, ConversationMessageInput $conversationMessageInput): JsonResponse
    {
        $user = $this->userService->getUser();

        $conversationMessageModel = new ConversationMessageModel;
        $conversationMessageModel->conversation = $conversation;
        $conversationMessageModel->owner = $user;
        $conversationMessageModel->content = $conversationMessageInput->message;

        $conversation->createUpdateAblePreUpdate();

        $command = CreateConversationMessageCommand::create($conversation, $conversationMessageModel, $user);
        $this->commandBus->dispatch($command);

        return new JsonResponse(status: Response::HTTP_CREATED);
    }
}
