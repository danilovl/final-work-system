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

namespace App\Domain\ConversationMessage\Bus\Query\ConversationMessage;

use App\Infrastructure\Interfaces\Bus\QueryHandlerInterface;
use App\Domain\ConversationMessage\Facade\ConversationMessageFacade;

readonly class GetConversationMessageQueryHandler implements QueryHandlerInterface
{
    public function __construct(private ConversationMessageFacade $conversationMessageFacade) {}

    public function __invoke(GetConversationMessageQuery $query): GetConversationMessageQueryResult
    {
        $conversationMessage = $this->conversationMessageFacade->getConversationMessage($query->id);

        return new GetConversationMessageQueryResult($conversationMessage);
    }
}
