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

namespace App\Domain\Comment\Bus\Query\EventComment;

use App\Infrastructure\Interfaces\Bus\QueryHandlerInterface;
use App\Domain\Comment\Facade\CommentFacade;

readonly class GetEventCommentQueryHandler implements QueryHandlerInterface
{
    public function __construct(private CommentFacade $commentFacade) {}

    public function __invoke(GetEventCommentQuery $query): GetEventCommentQueryResult
    {
        $comment = $this->commentFacade->findByOwnerEvent(
            user: $query->user,
            event: $query->event
        );

        return new GetEventCommentQueryResult($comment);
    }
}
