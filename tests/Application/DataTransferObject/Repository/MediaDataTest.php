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

use App\Application\DataTransferObject\Repository\MediaData;
use App\Domain\MediaType\Entity\MediaType;
use App\Domain\User\Entity\User;
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
