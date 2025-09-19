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

namespace App\Domain\ConversationMessage\Security\Voter;

use App\Application\Constant\VoterSupportConstant;
use App\Domain\Conversation\Service\ConversationService;
use App\Domain\ConversationMessage\Entity\ConversationMessage;
use App\Domain\User\Entity\User;
use LogicException;
use Override;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class ConversationMessageVoter extends Voter
{
    public const array SUPPORTS = [
        VoterSupportConstant::CHANGE_READ_MESSAGE_STATUS->value
    ];

    public function __construct(private readonly ConversationService $conversationService) {}

    #[Override]
    protected function supports(string $attribute, mixed $subject): bool
    {
        return $subject instanceof ConversationMessage && in_array($attribute, self::SUPPORTS, true);
    }

    #[Override]
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof User) {
            return false;
        }

        if (!$subject instanceof ConversationMessage) {
            return false;
        }

        if ($attribute === VoterSupportConstant::CHANGE_READ_MESSAGE_STATUS->value) {
            return $this->changeReadMessageStatus($subject, $user);
        }

        throw new LogicException('This code should not be reached!');
    }

    private function changeReadMessageStatus(ConversationMessage $conversationMessage, User $user): bool
    {
        $conversation = $conversationMessage->getConversation();

        return $this->conversationService->isParticipant($conversation, $user) && !$conversationMessage->isOwner($user);
    }
}
