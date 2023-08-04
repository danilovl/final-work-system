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

namespace App\Tests\Unit\Domain\Work\DataTransferObject;

use App\Domain\User\Entity\User;
use App\Domain\Work\DataTransferObject\WorkRepositoryData;
use App\Domain\WorkStatus\Entity\WorkStatus;
use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class WorkRepositoryDataTest extends TestCase
{
    #[DataProvider('dataProvider')]
    public function testToArray(array $data): void
    {
        $conversationMessageStatus = WorkRepositoryData::createFromArray($data);

        $this->assertEquals(
            $data,
            $conversationMessageStatus->toArray()
        );
    }

    public static function dataProvider(): Generator
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
