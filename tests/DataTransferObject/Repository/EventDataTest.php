<?php declare(strict_types=1);

namespace App\Tests\DataTransferObject\Repository;

use App\DataTransferObject\Repository\EventData;
use DateTime;
use App\Entity\{
    User,
    EventType
};
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
                'user' => new class extends User {},
                'startDate' => new DateTime,
                'endDate' => new DateTime,
                'eventType' => new class extends EventType {}
            ]
        ];
    }
}
