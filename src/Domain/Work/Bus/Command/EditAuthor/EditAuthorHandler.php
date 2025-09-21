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

namespace App\Domain\Work\Bus\Command\EditAuthor;

use App\Application\Interfaces\Bus\CommandHandlerInterface;
use App\Domain\User\Factory\UserFactory;
use App\Domain\Work\EventDispatcher\WorkEventDispatcher;

readonly class EditAuthorHandler implements CommandHandlerInterface
{
    public function __construct(
        private UserFactory $userFactory,
        private WorkEventDispatcher $workEventDispatcher
    ) {}

    public function __invoke(EditAuthorCommand $command): void
    {
        $this->userFactory->flushFromModel($command->userModel, $command->user);
        $this->workEventDispatcher->onWorkEditAuthor($command->work);
    }
}
