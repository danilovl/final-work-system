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

use App\Application\Interfaces\Bus\CommandInterface;
use App\Domain\WorkCategory\Model\WorkCategoryModel;
use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage('sync')]
readonly class CreateWorkCategoryCommand implements CommandInterface
{
    private function __construct(public WorkCategoryModel $workCategoryModel) {}

    public static function create(WorkCategoryModel $workCategoryModel): self
    {
        return new self($workCategoryModel);
    }
}
