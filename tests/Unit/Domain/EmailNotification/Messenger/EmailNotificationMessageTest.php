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

namespace App\Tests\Unit\Domain\EmailNotification\Messenger;

use App\Domain\EmailNotification\Messenger\EmailNotificationMessage;
use PHPUnit\Framework\TestCase;

class EmailNotificationMessageTest extends TestCase
{
    public function testGenerateUuid(): void
    {
        $emailNotificationMessage = new EmailNotificationMessage(
            locale: 'en',
            subject: 'subject',
            to: 'test@example.com',
            from: 'test@example.com',
            template: 'template',
            templateParameters: [
                'key' => 'value',
            ],
            uuid: 'uuid'
        );

        $emailNotificationMessage->generateUuid();

        $this->assertSame($emailNotificationMessage->uuid, $emailNotificationMessage->uuid);
    }
}
