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

namespace App\Tests\Unit\Domain\ConversationMessage\DataTransferObject;

use App\Domain\Conversation\Entity\Conversation;
use App\Domain\ConversationMessageStatus\DataTransferObject\ConversationMessageStatusRepositoryData;
use App\Domain\ConversationMessageStatusType\Entity\ConversationMessageStatusType;
use App\Domain\User\Entity\User;
use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ConversationMessageStatusRepositoryDataTest extends TestCase
{
    #[DataProvider('dataProvider')]
    public function testToArray(array $data): void
    {
        $conversationMessageStatus = ConversationMessageStatusRepositoryData::createFromArray($data);

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
                'user' => new class ( ) extends User {},
                'conversation' => new class ( ) extends Conversation {},
                'type' => new class ( ) extends ConversationMessageStatusType {}
            ]
        ];
    }
}
