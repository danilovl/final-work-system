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

namespace App\Domain\ConversationMessageStatus\Bus\Query\MessageTypesByConversationUser;

use App\Infrastructure\Interfaces\Bus\QueryHandlerInterface;
use App\Domain\ConversationMessageStatus\Facade\ConversationMessageStatusFacade;

readonly class GetMessageTypesByConversationUserQueryHandler implements QueryHandlerInterface
{
    public function __construct(private ConversationMessageStatusFacade $conversationMessageStatusFacade) {}

    public function __invoke(GetMessageTypesByConversationUserQuery $query): GetMessageTypesByConversationUserQueryResult
    {
        $rows = $this->conversationMessageStatusFacade->listMessageTypesByConversationUser(
            conversationId: $query->conversationId,
            user: $query->user
        );

        return new GetMessageTypesByConversationUserQueryResult($rows);
    }
}
