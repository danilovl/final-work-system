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

namespace App\Domain\Conversation\Controller;

use App\Application\Constant\VoterSupportConstant;
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
use Symfony\Component\HttpFoundation\{
    RedirectResponse,
    Request,
    Response
};
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ConversationController extends AbstractController
{
    public function __construct(
        private readonly ConversationCreateHandle $conversationCreateHandle,
        private readonly ConversationListHandle $conversationListHandle,
        private readonly ConversationDetailHandle $conversationDetailHandle,
        private readonly ConversationCreateWorkHandle $conversationCreateWorkHandle,
        private readonly ConversationLastMessageHandle $conversationLastMessageHandle
    ) {}

    public function create(Request $request): Response
    {
        return $this->conversationCreateHandle->handle($request);
    }

    public function list(Request $request): Response
    {
        return $this->conversationListHandle->handle($request);
    }

    public function detail(Request $request, Conversation $conversation): Response
    {
        $this->denyAccessUnlessGranted(VoterSupportConstant::VIEW, $conversation);

        return $this->conversationDetailHandle->handle($request, $conversation);
    }

    public function createWorkConversation(
        #[MapEntity(mapping: ['id_work' => 'id'])] Work $work,
        #[MapEntity(mapping: ['id_user_one' => 'id'])] User $userOne,
        #[MapEntity(mapping: ['id_user_two' => 'id'])] User $userTwo
    ): RedirectResponse {
        return $this->conversationCreateWorkHandle->handle($work, $userOne, $userTwo);
    }

    public function lastMessage(Request $request, Conversation $conversation): Response
    {
        $this->denyAccessUnlessGranted(VoterSupportConstant::VIEW, $conversation);

        return $this->conversationLastMessageHandle->handle($request, $conversation);
    }
}
