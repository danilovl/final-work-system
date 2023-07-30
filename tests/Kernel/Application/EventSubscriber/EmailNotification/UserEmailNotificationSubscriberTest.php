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

namespace App\Tests\Kernel\Application\EventSubscriber\EmailNotification;

use App\Application\EventSubscriber\EmailNotification\UserEmailNotificationSubscriber;

class UserEmailNotificationSubscriberTest extends BaseEmailNotificationSubscriber
{
    protected string $classSubscriber = UserEmailNotificationSubscriber::class;
}
