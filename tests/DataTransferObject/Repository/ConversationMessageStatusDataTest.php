<?php

namespace App\Tests\DataTransferObject\Repository;

use App\DataTransferObject\Repository\ConversationMessageStatusData;
use App\Entity\{
    User,
    Conversation,
    ConversationMessageStatusType
};
use Generator;
use PHPUnit\Framework\TestCase;

class ConversationMessageStatusDataTest extends TestCase
{
    /**
     * @dataProvider dataProvider
     */
    public function testToArray(array $data): void
    {
        $conversationMessageStatus = ConversationMessageStatusData::createFromArray($data);

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
                'conversation' => null,
                'type' => null
            ]
        ];

        yield [
            [
                'user' => new User,
                'conversation' => new Conversation,
                'type' => new ConversationMessageStatusType
            ]
        ];

        yield [
            [
                'user' => new class extends User{},
                'conversation' => new class extends Conversation{},
                'type' => new class extends ConversationMessageStatusType{}
            ]
        ];
    }
}
