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

namespace App\Tests\Unit\Domain\Media\DataTransferObject;

use App\Domain\Media\DataTransferObject\MediaRepositoryData;
use App\Domain\MediaType\Entity\MediaType;
use App\Domain\User\Entity\User;
use Generator;
use PHPUnit\Framework\TestCase;

class MediaRepositoryDataTest extends TestCase
{
    /**
     * @dataProvider dataProvider
     */
    public function testToArray(array $data): void
    {
        $conversationMessageStatus = MediaRepositoryData::createFromArray($data);

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
