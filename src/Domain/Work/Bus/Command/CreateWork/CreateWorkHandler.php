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

namespace App\Domain\Work\Bus\Command\CreateWork;

use App\Application\Interfaces\Bus\CommandHandlerInterface;
use App\Domain\Work\Entity\Work;
use App\Domain\Work\Factory\WorkFactory;

readonly class CreateWorkHandler implements CommandHandlerInterface
{
    public function __construct(private WorkFactory $workFactory) {}

    public function __invoke(CreateWorkCommand $command): Work
    {
        return $this->workFactory->flushFromModel($command->workModel);
    }
}
