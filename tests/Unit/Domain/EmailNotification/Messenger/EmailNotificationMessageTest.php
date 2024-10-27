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

namespace Domain\EmailNotification\Messenger;

use App\Domain\EmailNotification\Messenger\EmailNotificationMessage;
use PHPUnit\Framework\TestCase;

class EmailNotificationMessageTest extends TestCase
{
    public function testGenerateUuid(): void
    {
        $emailNotificationMessage = EmailNotificationMessage::createFromArray([
            'subject' => 'subject',
            'template' => 'template',
            'templateParameters' => [
                'key' => 'value',
            ],
            'locale' => 'en',
            'from' => 'test@example.com',
            'to' => 'test@example.com',
            'uuid' => 'uuid',
        ]);

        $emailNotificationMessage->generateUuid();

        $this->assertSame($emailNotificationMessage->uuid, $emailNotificationMessage->uuid);
    }
}
