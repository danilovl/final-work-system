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

namespace App\Domain\ConversationMessage\Bus\Query\UnreadMessagesByUser;

use App\Application\Interfaces\Bus\QueryHandlerInterface;
use App\Domain\ConversationMessage\Facade\ConversationMessageFacade;

readonly class GetUnreadMessagesByUserQueryHandler implements QueryHandlerInterface
{
    public function __construct(private ConversationMessageFacade $conversationMessageFacade) {}

    public function __invoke(GetUnreadMessagesByUserQuery $query): GetUnreadMessagesByUserQueryResult
    {
        $messages = $this->conversationMessageFacade->getUnreadMessagesByUser(
            user: $query->user,
            limit: $query->limit
        );

        return new GetUnreadMessagesByUserQueryResult($messages);
    }
}
