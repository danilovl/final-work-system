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

namespace App\Tests\Unit\Domain\SystemEvent\EventSubscriber;

use App\Domain\SystemEvent\EventSubscriber\TaskSystemEventSubscriber;

class TaskSystemEventSubscriberTest extends BaseSystemEventSubscriber
{
    protected string $classSubscriber = TaskSystemEventSubscriber::class;
}

