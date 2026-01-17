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

namespace App\Domain\Conversation\Controller;

use App\Application\Constant\VoterSupportConstant;
use App\Infrastructure\Service\AuthorizationCheckerService;
use App\Domain\Conversation\Entity\Conversation;
use App\Domain\Conversation\Http\{
    ConversationLastMessageHandle,
    ConversationListHandle,
    ConversationCreateHandle,
    ConversationDetailHandle,
    ConversationCreateWorkHandle
};
use App\Domain\User\Entity\User;
use App\Domain\Work\Entity\Work;
use Danilovl\HashidsBundle\Attribute\HashidsRequestConverterAttribute;
use Symfony\Component\HttpFoundation\{
    Request,
    Response,
    RedirectResponse
};
use Symfony\Bridge\Doctrine\Attribute\MapEntity;

readonly class ConversationController
{
    public function __construct(
        private AuthorizationCheckerService $authorizationCheckerService,
        private ConversationCreateHandle $conversationCreateHandle,
        private ConversationListHandle $conversationListHandle,
        private ConversationDetailHandle $conversationDetailHandle,
        private ConversationCreateWorkHandle $conversationCreateWorkHandle,
        private ConversationLastMessageHandle $conversationLastMessageHandle
    ) {}

    public function create(Request $request): Response
    {
        return $this->conversationCreateHandle->__invoke($request);
    }

    public function list(Request $request): Response
    {
        return $this->conversationListHandle->__invoke($request);
    }

    public function detail(Request $request, Conversation $conversation): Response
    {
        $this->authorizationCheckerService->denyAccessUnlessGranted(VoterSupportConstant::VIEW->value, $conversation);

        return $this->conversationDetailHandle->__invoke($request, $conversation);
    }

    #[HashidsRequestConverterAttribute(requestAttributesKeys: ['id_work', 'id_user_one', 'id_user_two'])]
    public function createWorkConversation(
        #[MapEntity(mapping: ['id_work' => 'id'])] Work $work,
        #[MapEntity(mapping: ['id_user_one' => 'id'])] User $userOne,
        #[MapEntity(mapping: ['id_user_two' => 'id'])] User $userTwo
    ): RedirectResponse {
        return $this->conversationCreateWorkHandle->__invoke($work, $userOne, $userTwo);
    }

    public function lastMessage(Request $request, Conversation $conversation): Response
    {
        $this->authorizationCheckerService->denyAccessUnlessGranted(VoterSupportConstant::VIEW->value, $conversation);

        return $this->conversationLastMessageHandle->__invoke($request, $conversation);
    }
}
