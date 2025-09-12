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

namespace App\Domain\Comment\Bus\Command\CreateComment;

use App\Application\Interfaces\Bus\CommandInterface;
use App\Domain\Comment\Entity\Comment;
use App\Domain\Comment\Model\CommentModel;
use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage('sync')]
readonly class CreateCommentCommand implements CommandInterface
{
    private function __construct(
        public CommentModel $commentModel,
        public ?Comment $comment = null
    ) {}

    public static function create(CommentModel $commentModel, ?Comment $comment = null): self
    {
        return new self($commentModel, $comment);
    }
}
