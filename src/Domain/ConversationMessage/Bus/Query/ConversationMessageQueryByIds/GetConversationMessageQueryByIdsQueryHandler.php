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

namespace App\Domain\ConversationMessage\Bus\Query\ConversationMessageQueryByIds;

use App\Infrastructure\Interfaces\Bus\QueryHandlerInterface;
use App\Domain\ConversationMessage\Facade\ConversationMessageFacade;

readonly class GetConversationMessageQueryByIdsQueryHandler implements QueryHandlerInterface
{
    public function __construct(private ConversationMessageFacade $conversationMessageFacade) {}

    public function __invoke(GetConversationMessageQueryByIdsQuery $query): GetConversationMessageQueryByIdsQueryResult
    {
        $query = $this->conversationMessageFacade->queryByIds($query->ids);

        return new GetConversationMessageQueryByIdsQueryResult($query);
    }
}
