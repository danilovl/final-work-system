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

namespace App\Domain\ConversationMessageStatus\Bus\Query\ConversationMessageStatusByMessageUser;

use App\Application\Interfaces\Bus\QueryHandlerInterface;
use App\Domain\ConversationMessageStatus\Facade\ConversationMessageStatusFacade;

readonly class GetConversationMessageStatusByMessageUserQueryHandler implements QueryHandlerInterface
{
    public function __construct(private ConversationMessageStatusFacade $conversationMessageStatusFacade) {}

    public function __invoke(GetConversationMessageStatusByMessageUserQuery $query): GetConversationMessageStatusByMessageUserQueryResult
    {
        $conversationMessageStatus = $this->conversationMessageStatusFacade->findByMessageUser(
            conversationMessage: $query->conversationMessage,
            user: $query->user
        );

        return new GetConversationMessageStatusByMessageUserQueryResult($conversationMessageStatus);
    }
}
