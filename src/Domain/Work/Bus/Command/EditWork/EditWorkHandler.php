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

namespace App\Domain\Work\Bus\Command\EditWork;

use App\Application\Interfaces\Bus\CommandHandlerInterface;
use App\Domain\Work\EventDispatcher\WorkEventDispatcher;
use App\Domain\Work\Factory\WorkFactory;

readonly class EditWorkHandler implements CommandHandlerInterface
{
    public function __construct(
        private WorkFactory $workFactory,
        private WorkEventDispatcher $workEventDispatcher
    ) {}

    public function __invoke(EditWorkCommand $command): void
    {
        $this->workFactory->flushFromModel($command->workModel, $command->work);
        $this->workEventDispatcher->onWorkEdit($command->work);
    }
}
