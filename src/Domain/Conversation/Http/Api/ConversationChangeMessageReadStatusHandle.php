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
use App\Domain\Conversation\Bus\Command\ChangeReadMessageStatus\ChangeReadMessageStatusCommand;
use App\Domain\ConversationMessage\Entity\ConversationMessage;
use App\Domain\User\Service\UserService;
use Symfony\Component\HttpFoundation\{
    Response,
    JsonResponse
};

readonly class ConversationChangeMessageReadStatusHandle
{
    public function __construct(
        private UserService $userService,
        private CommandBusInterface $commandBus
    ) {}

    public function __invoke(ConversationMessage $conversationMessage): JsonResponse
    {
        $user = $this->userService->getUser();

        $command = ChangeReadMessageStatusCommand::create($user, $conversationMessage);
        $this->commandBus->dispatch($command);

        return new JsonResponse(status: Response::HTTP_OK);
    }
}
