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

namespace App\Domain\ConversationMessage\Bus\Query\TotalUnreadMessagesByUser;

use App\Application\Interfaces\Bus\QueryHandlerInterface;
use App\Domain\ConversationMessage\Facade\ConversationMessageFacade;

readonly class GetTotalUnreadMessagesByUserQueryHandler implements QueryHandlerInterface
{
    public function __construct(private ConversationMessageFacade $conversationMessageFacade) {}

    public function __invoke(GetTotalUnreadMessagesByUserQuery $query): GetTotalUnreadMessagesByUserQueryResult
    {
        $count = $this->conversationMessageFacade->getTotalUnreadMessagesByUser($query->user);

        return new GetTotalUnreadMessagesByUserQueryResult($count);
    }
}
