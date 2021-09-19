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

namespace App\Controller;

use App\Constant\VoterSupportConstant;
use App\Entity\{
    Work,
    Conversation
};
use App\Entity\User;
use Symfony\Component\HttpFoundation\{
    Request,
    Response,
    RedirectResponse
};
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

class ConversationController extends BaseController
{
    public function create(Request $request): Response
    {
        return $this->get('app.http_handle.conversation.create')->handle($request);
    }

    public function list(Request $request): Response
    {
        return $this->get('app.http_handle.conversation.list')->handle($request);
    }

    public function detail(Request $request, Conversation $conversation): Response
    {
        $this->denyAccessUnlessGranted(VoterSupportConstant::VIEW, $conversation);

        return $this->get('app.http_handle.conversation.detail')->handle($request, $conversation);
    }

    /**
     * @ParamConverter("work", class="App\Entity\Work", options={"id" = "id_work"})
     * @ParamConverter("userOne", class="App\Entity\User", options={"id" = "id_user_one"})
     * @ParamConverter("userTwo", class="App\Entity\User", options={"id" = "id_user_two"})
     */
    public function createWorkConversation(
        Work $work,
        User $userOne,
        User $userTwo
    ): RedirectResponse {
        return $this->get('app.http_handle.conversation.create_work')->handle($work, $userOne, $userTwo);
    }

    public function lastMessage(Request $request, Conversation $conversation): Response
    {
        $this->denyAccessUnlessGranted(VoterSupportConstant::VIEW, $conversation);

        return $this->get('app.http_handle.conversation.last_message')->handle($request, $conversation);
    }
}
