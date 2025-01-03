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

namespace App\Domain\Task\EventDispatcher\GenericEvent;

use App\Domain\Task\Entity\Task;

readonly class TaskGenericEvent
{
    public function __construct(public Task $task, public ?string $type = null) {}
}
