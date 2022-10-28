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

namespace App\Domain\EmailNotificationQueue;

class EmailNotificationQueueModel
{
    public string $subject;
    public string $to;
    public string $from;
    public string $body;
    public bool $success = false;
    public string $uuid;
}
