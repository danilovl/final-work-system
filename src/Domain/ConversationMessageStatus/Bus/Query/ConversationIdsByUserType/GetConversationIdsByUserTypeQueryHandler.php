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

namespace App\Domain\ConversationMessageStatus\Bus\Query\ConversationIdsByUserType;

use App\Application\Interfaces\Bus\QueryHandlerInterface;
use App\Domain\ConversationMessageStatus\Facade\ConversationMessageStatusFacade;

readonly class GetConversationIdsByUserTypeQueryHandler implements QueryHandlerInterface
{
    public function __construct(private ConversationMessageStatusFacade $conversationMessageStatusFacade) {}

    public function __invoke(GetConversationIdsByUserTypeQuery $query): GetConversationIdsByUserTypeQueryResult
    {
        $conversationIds = $this->conversationMessageStatusFacade->listConversationIdsByUserType(
            user: $query->user,
            type: $query->type
        );

        return new GetConversationIdsByUserTypeQueryResult($conversationIds);
    }
}
