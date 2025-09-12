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

use App\Application\Interfaces\Bus\CommandHandlerInterface;
use App\Domain\Comment\Entity\Comment;
use App\Domain\Comment\Factory\CommentFactory;

readonly class CreateCommentHandler implements CommandHandlerInterface
{
    public function __construct(private CommentFactory $commentFactory) {}

    public function __invoke(CreateCommentCommand $command): Comment
    {
        return $this->commentFactory->createFromModel($command->commentModel, $command->comment);
    }
}
