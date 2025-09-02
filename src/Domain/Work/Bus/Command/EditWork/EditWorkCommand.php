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

use App\Application\Interfaces\Bus\CommandInterface;
use App\Domain\Work\Entity\Work;
use App\Domain\Work\Model\WorkModel;
use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage('sync')]
readonly class EditWorkCommand implements CommandInterface
{
    private function __construct(public Work $work, public WorkModel $workModel) {}

    public static function create(Work $work, WorkModel $workModel): self
    {
        return new self($work, $workModel);
    }
}
