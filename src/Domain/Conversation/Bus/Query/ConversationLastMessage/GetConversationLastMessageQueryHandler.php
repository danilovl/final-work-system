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

namespace App\Domain\Conversation\Bus\Query\ConversationLastMessage;

use App\Domain\Conversation\Facade\ConversationMessageFacade;
use App\Domain\Conversation\Service\MessageHighlightService;
use Danilovl\ParameterBundle\Interfaces\ParameterServiceInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
readonly class GetConversationLastMessageQueryHandler
{
    public function __construct(
        private ConversationMessageFacade $conversationMessageFacade,
        private ParameterServiceInterface $parameterService,
        private MessageHighlightService $messageHighlightService
    ) {}

    public function __invoke(GetConversationLastMessageQuery $query): GetConversationLastMessageQueryResult
    {
        $limit = $this->parameterService->getInt('pagination.conversation.message_list');

        $conversationMessages = $this->conversationMessageFacade->getMessagesByConversation($query->conversation, $limit);
        if ($query->search) {
            $this->messageHighlightService->addHighlight($conversationMessages, $query->search);
        }

        return new GetConversationLastMessageQueryResult($conversationMessages);
    }
}
