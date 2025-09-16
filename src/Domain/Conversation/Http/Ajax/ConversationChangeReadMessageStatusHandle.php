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
use App\Application\Service\RequestService;
use App\Domain\Conversation\Bus\Command\ChangeReadMessageStatus\ChangeReadMessageStatusCommand;
use App\Domain\ConversationMessage\Entity\ConversationMessage;
use App\Domain\User\Service\UserService;
use Symfony\Component\HttpFoundation\JsonResponse;

readonly class ConversationChangeReadMessageStatusHandle
{
    public function __construct(
        private RequestService $requestService,
        private UserService $userService,
        private CommandBusInterface $commandBus
    ) {}

    public function __invoke(ConversationMessage $conversationMessage): JsonResponse
    {
        $user = $this->userService->getUser();

        $command = ChangeReadMessageStatusCommand::create($user, $conversationMessage);
        $this->commandBus->dispatch($command);

        return $this->requestService->createAjaxJson(AjaxJsonTypeConstant::SAVE_SUCCESS);
    }
}
