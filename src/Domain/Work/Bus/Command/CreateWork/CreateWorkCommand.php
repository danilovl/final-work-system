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

use App\Application\Interfaces\Bus\CommandInterface;
use App\Domain\Work\Model\WorkModel;
use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage('sync')]
readonly class CreateWorkCommand implements CommandInterface
{
    private function __construct(public WorkModel $workModel) {}

    public static function create(WorkModel $workModel): self
    {
        return new self($workModel);
    }
}
