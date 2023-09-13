<?php declare(strict_types=1);

/*
 *
 * This file is part of the FinalWorkSystem project.
 * (c) Vladimir Danilov
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace App\Tests\Unit\Domain\ConversationMessage\Security\Voter;

use App\Application\Constant\VoterSupportConstant;
use App\Domain\Conversation\Service\ConversationService;
use App\Domain\ConversationMessage\Entity\ConversationMessage;
use App\Domain\ConversationMessage\Security\Voter\ConversationMessageVoter;
use PHPUnit\Framework\TestCase;

class ConversationMessageVoterTest extends TestCase
{
    private readonly ConversationMessageVoter $conversationMessageVoter;

    protected function setUp(): void
    {
        $this->conversationMessageVoter = new class(new ConversationService) extends ConversationMessageVoter {
            public function supportsPublic(string $attribute, mixed $subject): bool
            {
                return $this->supports($attribute, $subject);
            }
        };
    }

    public function testSupports(): void
    {
        $conversationMessage = $this->createMock(ConversationMessage::class);

        foreach (ConversationMessageVoter::SUPPORTS as $support) {
            $this->assertTrue($this->conversationMessageVoter->supportsPublic($support, $conversationMessage));
        }

        $this->assertFalse($this->conversationMessageVoter->supportsPublic('invalid_attribute', $conversationMessage));
        $this->assertFalse($this->conversationMessageVoter->supportsPublic(VoterSupportConstant::VIEW->value, 'invalid_subject'));
    }
}
