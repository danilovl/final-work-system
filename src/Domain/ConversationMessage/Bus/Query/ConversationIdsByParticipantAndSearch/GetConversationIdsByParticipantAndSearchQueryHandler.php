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

namespace App\Domain\ConversationMessage\Bus\Query\ConversationIdsByParticipantAndSearch;

use App\Infrastructure\Interfaces\Bus\QueryHandlerInterface;
use App\Domain\ConversationMessage\Repository\Elastica\ElasticaConversationMessageRepository;

readonly class GetConversationIdsByParticipantAndSearchQueryHandler implements QueryHandlerInterface
{
    public function __construct(private ElasticaConversationMessageRepository $elasticaConversationMessageRepository) {}

    public function __invoke(GetConversationIdsByParticipantAndSearchQuery $query): GetConversationIdsByParticipantAndSearchQueryResult
    {
        $ids = $this->elasticaConversationMessageRepository->getConversationIdsByParticipantAndSearch(
            user: $query->user,
            search: $query->search
        );

        return new GetConversationIdsByParticipantAndSearchQueryResult($ids);
    }
}
