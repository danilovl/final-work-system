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

namespace App\Domain\ConversationType\Bus\Query\ConversationTypeList;

use App\Infrastructure\Interfaces\Bus\QueryHandlerInterface;
use App\Domain\ConversationType\Facade\ConversationTypeFacade;

readonly class GetConversationTypeListQueryHandler implements QueryHandlerInterface
{
    public function __construct(private ConversationTypeFacade $conversationTypeFacade) {}

    public function __invoke(GetConversationTypeListQuery $query): GetConversationTypeListQueryResult
    {
        $conversationTypes = $this->conversationTypeFacade->list();

        return new GetConversationTypeListQueryResult($conversationTypes);
    }
}
