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

namespace App\Tests\Unit\Domain\Conversation\Security\Voter;

use App\Application\Constant\VoterSupportConstant;
use App\Domain\Conversation\Entity\Conversation;
use App\Domain\Conversation\Security\Voter\ConversationVoter;
use App\Domain\Conversation\Service\ConversationService;
use App\Tests\Mock\Application\Security\Voter\VoterInterfaceMock;
use PHPUnit\Framework\TestCase;

class ConversationVoterTest extends TestCase
{
    private VoterInterfaceMock $conversationVoter;

    protected function setUp(): void
    {
        $this->conversationVoter = new class(new ConversationService) extends ConversationVoter implements VoterInterfaceMock{
            public function supportsPublic(string $attribute, mixed $subject): bool
            {
                return $this->supports($attribute, $subject);
            }
        };
    }

    public function testSupports(): void
    {
        $conversationMessage = $this->createMock(Conversation::class);

        foreach (ConversationVoter::SUPPORTS as $support) {
            $this->assertTrue($this->conversationVoter->supportsPublic($support, $conversationMessage));
        }

        $this->assertFalse($this->conversationVoter->supportsPublic('invalid_attribute', $conversationMessage));
        $this->assertFalse($this->conversationVoter->supportsPublic(VoterSupportConstant::VIEW->value, 'invalid_subject'));
    }
}
