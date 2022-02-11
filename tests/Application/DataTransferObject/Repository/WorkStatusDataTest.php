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

use App\Application\DataTransferObject\Repository\WorkStatusData;
use App\Domain\User\Entity\User;
use App\Domain\WorkStatus\Entity\WorkStatus;
use Generator;
use PHPUnit\Framework\TestCase;

class WorkStatusDataTest extends TestCase
{
    /**
     * @dataProvider dataProvider
     */
    public function testToArray(array $data): void
    {
        $conversationMessageStatus = WorkStatusData::createFromArray($data);

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
