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

namespace App\Tests\Unit\Domain\WorkStatus\DataTransferObject;

use App\Domain\User\Entity\User;
use App\Domain\WorkStatus\DataTransferObject\WorkStatusRepositoryData;
use App\Domain\WorkStatus\Entity\WorkStatus;
use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class WorkStatusRepositoryDataTest extends TestCase
{
    #[DataProvider('dataProvider')]
    public function testToArray(array $data): void
    {
        $conversationMessageStatus = WorkStatusRepositoryData::createFromArray($data);

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
