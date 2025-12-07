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

namespace App\Domain\WorkCategory\Bus\Command\CreateWorkCategory;

use App\Application\Interfaces\Bus\CommandHandlerInterface;
use App\Domain\WorkCategory\Entity\WorkCategory;
use App\Domain\WorkCategory\Factory\WorkCategoryFactory;

readonly class CreateWorkCategoryHandler implements CommandHandlerInterface
{
    public function __construct(private WorkCategoryFactory $workCategoryFactory) {}

    public function __invoke(CreateWorkCategoryCommand $command): WorkCategory
    {
        return $this->workCategoryFactory->flushFromModel($command->workCategoryModel);
    }
}
