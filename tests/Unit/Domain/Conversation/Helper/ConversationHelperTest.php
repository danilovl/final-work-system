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

namespace App\Tests\Unit\Domain\Conversation\Helper;

use App\Domain\Conversation\Entity\Conversation;
use App\Domain\Conversation\Helper\ConversationHelper;
use App\Domain\ConversationParticipant\Entity\ConversationParticipant;
use App\Domain\ConversationType\Constant\ConversationTypeConstant;
use App\Domain\ConversationType\Entity\ConversationType;
use App\Domain\User\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Generator;
use LogicException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ConversationHelperTest extends TestCase
{
    /**
     * @param int[] $participantIds
     */
    #[DataProvider('provideGetRecipientCases')]
    public function testGetRecipient(int $type, array $participantIds, int $userId, ?int $expectedRecipientId): void
    {
        $conversation = $this->createConversation(
            type: $type,
            participantIds: $participantIds
        );

        $recipient = ConversationHelper::getRecipient(
            conversation: $conversation,
            user: $this->createUser($userId)
        );

        $this->assertSame($expectedRecipientId, $recipient?->getId());
    }

    public static function provideGetRecipientCases(): Generator
    {
        yield 'work conversation returns other participant' => [
            'type' => ConversationTypeConstant::WORK->value,
            'participantIds' => [1, 2],
            'userId' => 1,
            'expectedRecipientId' => 2
        ];

        yield 'work conversation from other side' => [
            'type' => ConversationTypeConstant::WORK->value,
            'participantIds' => [1, 2],
            'userId' => 2,
            'expectedRecipientId' => 1
        ];

        yield 'work conversation without other participant' => [
            'type' => ConversationTypeConstant::WORK->value,
            'participantIds' => [1],
            'userId' => 1,
            'expectedRecipientId' => null
        ];

        yield 'group conversation has no recipient' => [
            'type' => ConversationTypeConstant::GROUP->value,
            'participantIds' => [1, 2, 3],
            'userId' => 1,
            'expectedRecipientId' => null
        ];
    }

    public function testGetRecipientThrowsOnMoreThanTwoParticipants(): void
    {
        $conversation = $this->createConversation(
            type: ConversationTypeConstant::WORK->value,
            participantIds: [1, 2, 3]
        );

        $this->expectException(LogicException::class);

        ConversationHelper::getRecipient(
            conversation: $conversation,
            user: $this->createUser(1)
        );
    }

    /**
     * @param int[] $participantIds
     */
    private function createConversation(int $type, array $participantIds): Conversation
    {
        $conversationType = new ConversationType;
        $conversationType->setId($type);

        $conversation = new Conversation;
        $conversation->setType($conversationType);

        $participants = [];
        foreach ($participantIds as $participantId) {
            $participant = new ConversationParticipant;
            $participant->setUser($this->createUser($participantId));
            $participants[] = $participant;
        }

        $conversation->setParticipants(new ArrayCollection($participants));

        return $conversation;
    }

    private function createUser(int $id): User
    {
        $user = new User;
        $user->setId($id);

        return $user;
    }
}
