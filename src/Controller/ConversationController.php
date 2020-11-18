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

use App\Exception\ConstantNotFoundException;
use App\Model\ConversationMessage\{
    ConversationMessageModel,
    ConversationComposeMessageModel
};
use App\Constant\{
    FlashTypeConstant,
    VoterSupportConstant,
    ConversationTypeConstant,
    ConversationMessageStatusTypeConstant
};
use App\Entity\{
    Work,
    Conversation
};
use App\Form\{
    ConversationMessageForm,
    ConversationComposeMessageForm
};
use App\Helper\ConversationHelper;
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
        $user = $this->getUser();

        $conversationService = $this->get('app.facade.conversation');
        $conversationParticipants = $conversationService->getConversationParticipants($user);

        ConversationHelper::getConversationOpposite($conversationParticipants, $user);
        ConversationHelper::usortCzechArray($conversationParticipants);

        if ($user->isSupervisor()) {
            $conversationParticipants = ConversationHelper::groupConversationsByCategorySorting($conversationParticipants);
        }

        $conversationModel = new ConversationComposeMessageModel;
        $form = $this
            ->createForm(ConversationComposeMessageForm::class, $conversationModel, [
                'user' => $user,
                'conversations' => $conversationParticipants
            ])
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $conversationService->processCreateConversation($user, $conversationModel);

            return $this->redirectToRoute('conversation_list');
        }

        $isUnreadMessages = $this->get('app.facade.conversation_message')
            ->isUnreadMessagesByRecipient($user);

        $this->get('app.seo_page')->setTitle('app.page.message_create');

        return $this->render('conversation/create.html.twig', [
            'isUnreadMessages' => $isUnreadMessages,
            'form' => $form->createView()
        ]);
    }

    public function list(Request $request): Response
    {
        $user = $this->getUser();

        $conversationsQuery = $this->get('app.facade.conversation')
            ->queryConversationsByUser($user);

        $pagination = $this->createPagination(
            $request,
            $conversationsQuery,
            $this->getParam('pagination.default.page'),
            $this->getParam('pagination.default.limit'),
            ['wrap-queries' => true]
        );

        $this->get('app.facade.conversation')
            ->setIsReadToConversations($pagination, $user);

        ConversationHelper::getConversationOpposite($pagination, $user);

        $isUnreadMessages = $this->get('app.facade.conversation_message')
            ->isUnreadMessagesByRecipient($user);

        $this->get('app.seo_page')->setTitle('app.page.conversation_list');

        return $this->render('conversation/list.html.twig', [
            'isUnreadMessages' => $isUnreadMessages,
            'conversations' => $pagination
        ]);
    }

    public function detail(
        Request $request,
        Conversation $conversation
    ): Response {
        $this->denyAccessUnlessGranted(VoterSupportConstant::VIEW, $conversation);

        $user = $this->getUser();
        $form = null;

        $conversationMessageModel = new ConversationMessageModel;
        $conversationMessageModel->conversation = $conversation;
        $conversationMessageModel->owner = $user;

        switch ($conversation->getType()->getId()) {
            case ConversationTypeConstant::WORK:
                $form = $this
                    ->createForm(ConversationMessageForm::class, $conversationMessageModel, [
                        'user' => $user
                    ])
                    ->handleRequest($request);
                break;
            case ConversationTypeConstant::GROUP:
                if ($conversation->isOwner($user)) {
                    $form = $this
                        ->createForm(ConversationMessageForm::class, $conversationMessageModel, [
                            'user' => $user
                        ])
                        ->handleRequest($request);
                } else {
                    $conversation->setParticipants(null);
                }
                break;
            default:
                throw new ConstantNotFoundException('Conversation type constant not found');
        }

        if ($form !== null && $form->isSubmitted()) {
            if ($form->isValid()) {
                $conversationMessage = $this->get('app.factory.conversation_message')
                    ->flushFromModel($conversationMessageModel);

                $this->get('app.factory.conversation')
                    ->createConversationMessageStatus(
                        $conversation,
                        $conversationMessage,
                        $user,
                        $conversation->getParticipants(),
                        ConversationMessageStatusTypeConstant::UNREAD
                    );

                $this->get('app.event_dispatcher.conversation')
                    ->onConversationMessageCreate($conversationMessage);

                $this->addFlashTrans(FlashTypeConstant::SUCCESS, 'app.flash.form.create.success');
            } else {
                $this->addFlashTrans(FlashTypeConstant::WARNING, 'app.flash.form.create.warning');
                $this->addFlashTrans(FlashTypeConstant::ERROR, 'app.flash.form.create.error');
            }
        }

        $conversationMessagesQuery = $this->get('app.facade.conversation_message')
            ->queryMessagesByConversation($conversation);

        $pagination = $this->createPagination($request, $conversationMessagesQuery);

        $this->get('app.facade.conversation_message')->setIsReadToConversationMessages($pagination, $user);
        $this->get('app.seo_page')->setTitle($conversation->getTitle());

        return $this->render('conversation/detail.html.twig', [
            'conversation' => $conversation,
            'conversationMessages' => $pagination,
            'form' => $form ? $form->createView() : null
        ]);
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
        $conversationVariationService = $this->get('app.conversation_variation');

        if ($conversationVariationService->checker($work, $userOne, $userTwo)) {
            $workConversation = $work->checkConversation($userOne, $userTwo);

            if ($workConversation !== null) {
                $conversationService = $this->get('app.factory.conversation');
                $conversation = $conversationService->createConversation($userOne, ConversationTypeConstant::WORK, $work);
                $conversationService->createConversationParticipant($conversation, [$userOne, $userTwo]);

                $this->addFlashTrans(FlashTypeConstant::SUCCESS, 'app.flash.form.create.success');

                return $this->redirectToRoute('conversation_detail', [
                    'id' => $this->hashIdEncode($conversation->getId())
                ]);
            }

            return $this->redirectToRoute('conversation_detail', [
                'id' => $this->hashIdEncode($workConversation->getId())
            ]);
        }

        $this->addFlashTrans(FlashTypeConstant::WARNING, 'app.flash.form.create.warning');
        $this->addFlashTrans(FlashTypeConstant::ERROR, 'app.flash.form.create.error');

        return $this->redirectToRoute('work_detail', [
            'id' => $this->hashIdEncode($work->getId())
        ]);
    }

    public function lastMessage(
        Request $request,
        Conversation $conversation
    ): Response {
        $this->denyAccessUnlessGranted(VoterSupportConstant::VIEW, $conversation);

        $conversationMessages = $this->get('app.facade.conversation_message')
            ->getMessagesByConversation($conversation, $this->getParam('pagination.conversation.message_list'));

        $this->get('app.seo_page')->setTitle($conversation->getTitle());

        return $this->render($this->ajaxOrNormalFolder($request, 'conversation/last_message.html.twig'), [
            'conversation' => $conversation,
            'conversationMessages' => $conversationMessages
        ]);
    }
}
