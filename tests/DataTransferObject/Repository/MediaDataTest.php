<?php

namespace App\Tests\DataTransferObject\Repository;

use App\DataTransferObject\Repository\MediaData;
use App\Entity\{
    User,
    MediaType
};
use Generator;
use PHPUnit\Framework\TestCase;

class MediaDataTest extends TestCase
{
    /**
     * @dataProvider dataProvider
     */
    public function testToArray(array $data): void
    {
        $conversationMessageStatus = MediaData::createFromArray($data);

        $this->assertEquals(
            $data,
            $conversationMessageStatus->toArray()
        );
    }

    public function dataProvider(): Generator
    {
        yield [
            [
                'users' => null,
                'active' => null,
                'type' => null,
                'criteria' => null
            ]
        ];

        yield [
            [
                'users' => null,
                'active' => true,
                'type' => new MediaType,
                'criteria' => []
            ]
        ];

        yield [
            [
                'users' => new User,
                'active' => false,
                'type' => [],
                'criteria' => []
            ]
        ];
    }
}
