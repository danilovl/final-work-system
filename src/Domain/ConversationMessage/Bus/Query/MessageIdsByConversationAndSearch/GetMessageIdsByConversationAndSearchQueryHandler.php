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

namespace App\Domain\ConversationMessage\Bus\Query\MessageIdsByConversationAndSearch;

use App\Infrastructure\Interfaces\Bus\QueryHandlerInterface;
use App\Domain\ConversationMessage\Repository\Elastica\ElasticaConversationMessageRepository;

readonly class GetMessageIdsByConversationAndSearchQueryHandler implements QueryHandlerInterface
{
    public function __construct(private ElasticaConversationMessageRepository $elasticaConversationMessageRepository) {}

    public function __invoke(GetMessageIdsByConversationAndSearchQuery $query): GetMessageIdsByConversationAndSearchQueryResult
    {
        $ids = $this->elasticaConversationMessageRepository->getMessageIdsByConversationAndSearch(
            conversation: $query->conversation,
            search: $query->search
        );

        return new GetMessageIdsByConversationAndSearchQueryResult($ids);
    }
}
