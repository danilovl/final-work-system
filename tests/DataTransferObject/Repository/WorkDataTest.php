<?php

namespace App\Tests\DataTransferObject\Repository;

use App\DataTransferObject\Repository\WorkData;
use App\Entity\{
    User,
    WorkStatus
};
use Generator;
use PHPUnit\Framework\TestCase;

class WorkDataTest extends TestCase
{
    /**
     * @dataProvider dataProvider
     */
    public function testToArray(array $data): void
    {
        $conversationMessageStatus = WorkData::createFromArray($data);

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
                'supervisor' => null,
                'type' => null,
                'workStatus' => null
            ]
        ];

        yield [
            [
                'user' => new User,
                'supervisor' => new User,
                'type' => null,
                'workStatus' => new WorkStatus
            ]
        ];

        yield [
            [
                'user' => new User,
                'supervisor' => null,
                'type' => null,
                'workStatus' => []
            ]
        ];
    }
}
