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

namespace App\Tests\Application\DataTransferObject\Repository;

use App\Application\DataTransferObject\Repository\EventData;
use App\Domain\EventType\Entity\EventType;
use App\Domain\User\Entity\User;
use DateTime;
use Generator;
use PHPUnit\Framework\TestCase;

class EventDataTest extends TestCase
{
    /**
     * @dataProvider dataProvider
     */
    public function testToArray(array $data): void
    {
        $conversationMessageStatus = EventData::createFromArray($data);

        $this->assertEquals(
            $data,
            $conversationMessageStatus->toArray()
        );
    }

    public function dataProvider(): Generator
    {
        yield [
            [
                'user' => null,
                'startDate' => null,
                'endDate' => null,
                'eventType' => null
            ]
        ];

        yield [
            [
                'user' => new User,
                'startDate' => new DateTime,
                'endDate' => new DateTime,
                'eventType' => new EventType
            ]
        ];

        yield [
            [
                'user' => new class extends User { },
                'startDate' => new DateTime,
                'endDate' => new DateTime,
                'eventType' => new class extends EventType { }
            ]
        ];
    }
}