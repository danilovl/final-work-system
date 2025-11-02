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

namespace App\Domain\Conversation\Bus\Query\ConversationList;

use App\Domain\Conversation\Entity\Conversation;
use Knp\Component\Pager\Pagination\PaginationInterface;

readonly class GetConversationListQueryResult
{
    /**
     * @param PaginationInterface<int, Conversation> $conversations
     */
    public function __construct(public PaginationInterface $conversations) {}
}
