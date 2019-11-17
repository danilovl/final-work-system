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

namespace FinalWork\FinalWorkBundle\Controller;

use Doctrine\ORM\OptimisticLockException;
use FinalWork\FinalWorkBundle\Exception\ConstantNotFoundException;
use Doctrine\ORM\ORMException;
use Exception;
use FinalWork\FinalWorkBundle\Model\ConversationMessage\{
    ConversationMessageModel,
    ConversationComposeMessageModel
};
use FinalWork\FinalWorkBundle\Constant\{
    FlashTypeConstant,
    VoterSupportConstant,
    ConversationTypeConstant,
    ConversationMessageStatusTypeConstant
};
use FinalWork\FinalWorkBundle\Entity\{
    Work,
    Conversation
};
use FinalWork\FinalWorkBundle\Form\{
    ConversationMessageForm,
    ConversationComposeMessageForm
};
use FinalWork\FinalWorkBundle\Helper\ConversationHelper;
use FinalWork\SonataUserBundle\Entity\User;
use LogicException;
use Symfony\Component\HttpFoundation\{
    Request,
    Response,
    RedirectResponse
};
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class ConversationController extends BaseController
{
    /**
     * @param Request $request
     * @return Response
     *
     * @throws Exception
     */
    public function createAction(Request $request): Response
    {
        $user = $this->getUser();

        $conversationService = $this->get('final_work.facade.conversation');
        $conversationParticipants = $conversationService->getConversationParticipants($user);

        ConversationHelper::getConversationOpposite($conversationParticipants, $user);
        ConversationHelper::usortCzechArray($conversationParticipants);

        if ($user->isSupervisor()) {
            $conversationParticipants = ConversationHelper::groupConversationsByCategorySorting($conversationParticipants);
        }

        $conversationModel = new ConversationComposeMessageModel;
        $form = $this->createForm(ConversationComposeMessageForm::class, $conversationModel, [
            'user' => $user,
            'conversations' => $conversationParticipants
        ]);

        if ($user->isSupervisor() === false) {
            $form->remove('name');
        }

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $conversationService->processCreateConversation($user, $conversationModel);

            return $this->redirectToRoute('conversation_list');
        }

        $this->get('final_work.seo_page')->setTitle('finalwork.page.message_create');

        return $this->render('@FinalWork/conversation/create.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @param Request $request
     * @return Response
     *
     * @throws ORMException
     */
    public function listAction(Request $request): Response
    {
        $user = $this->getUser();

        $conversationsQuery = $this->get('final_work.facade.conversation')
            ->queryConversationsByUser($user);

        $pagination = $this->createPagination(
            $request,
            $conversationsQuery,
            $this->getParam('pagination.default.page'),
            $this->getParam('pagination.default.limit'),
            ['wrap-queries' => true]
        );

        $this->get('final_work.facade.conversation')
            ->setIsReadToConversations($pagination, $user);

        ConversationHelper::getConversationOpposite($pagination, $user);
        $this->get('final_work.seo_page')->setTitle('finalwork.page.conversation_list');

        return $this->render('@FinalWork/conversation/list.html.twig', [
            'conversations' => $pagination
        ]);
    }

    /**
     * @param Request $request
     * @param Conversation $conversation
     * @return Response
     *
     * @throws ORMException
     */
    public function detailAction(
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
                $conversationMessage = $this->get('final_work.factory.conversation_message')
                    ->flushFromModel($conversationMessageModel);

                $this->get('final_work.factory.conversation')
                    ->createConversationMessageStatus(
                        $conversation,
                        $conversationMessage,
                        $user,
                        $conversation->getParticipants(),
                        ConversationMessageStatusTypeConstant::UNREAD
                    );

                $this->get('final_work.event_dispatcher.conversation')
                    ->onConversationMessageCreate($conversationMessage);

                $this->addFlashTrans(FlashTypeConstant::SUCCESS, 'finalwork.flash.form.create.success');
            } else {
                $this->addFlashTrans(FlashTypeConstant::WARNING, 'finalwork.flash.form.create.warning');
                $this->addFlashTrans(FlashTypeConstant::ERROR, 'finalwork.flash.form.create.error');
            }
        }

        $conversationMessagesQuery = $this->get('final_work.facade.conversation_message')
            ->queryMessagesByConversation($conversation);

        $pagination = $this->createPagination($request, $conversationMessagesQuery);

        $this->get('final_work.facade.conversation_message')->setIsReadToConversationMessages($pagination, $user);
        $this->get('final_work.seo_page')->setTitle($conversation->getTitle());

        return $this->render('@FinalWork/conversation/detail.html.twig', [
            'conversation' => $conversation,
            'conversationMessage' => $pagination,
            'form' => $form ? $form->createView() : null
        ]);
    }

    /**
     * @param Work $work
     * @param User $userOne
     * @param User $userTwo
     *
     * @ParamConverter("work", class="FinalWork\FinalWorkBundle\Entity\Work", options={"id" = "id_work"})
     * @ParamConverter("userOne", class="FinalWork\SonataUserBundle\Entity\User", options={"id" = "id_user_one"})
     * @ParamConverter("userTwo", class="FinalWork\SonataUserBundle\Entity\User", options={"id" = "id_user_two"})
     *
     * @return RedirectResponse
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function createWorkConversationAction(
        Work $work,
        User $userOne,
        User $userTwo
    ): RedirectResponse {
        $conversationVariationService = $this->get('final_work.conversation_variation');

        if ($conversationVariationService->checker($work, $userOne, $userTwo)) {
            $workConversation = $work->checkConversation($userOne, $userTwo);

            if ($workConversation !== null) {
                $conversationService = $this->get('final_work.factory.conversation');
                $conversation = $conversationService->createConversation($userOne, ConversationTypeConstant::WORK, $work);
                $conversationService->createConversationParticipant($conversation, [$userOne, $userTwo]);

                $this->addFlashTrans(FlashTypeConstant::SUCCESS, 'finalwork.flash.form.create.success');

                return $this->redirectToRoute('conversation_detail', [
                    'id' => $this->hashIdEncode($conversation->getId())
                ]);
            }

            return $this->redirectToRoute('conversation_detail', [
                'id' => $this->hashIdEncode($workConversation->getId())
            ]);
        }

        $this->addFlashTrans(FlashTypeConstant::WARNING, 'finalwork.flash.form.create.warning');
        $this->addFlashTrans(FlashTypeConstant::ERROR, 'finalwork.flash.form.create.error');

        return $this->redirectToRoute('work_detail', [
            'id' => $this->hashIdEncode($work->getId())
        ]);
    }

    /**
     * @param Request $request
     * @param Conversation $conversation
     * @return Response
     * @throws LogicException
     * @throws AccessDeniedException
     */
    public function lastMessageAction(
        Request $request,
        Conversation $conversation
    ): Response {
        $this->denyAccessUnlessGranted(VoterSupportConstant::VIEW, $conversation);

        $conversationMessages = $this->get('final_work.facade.conversation_message')
            ->getMessagesByConversation($conversation, $this->getParam('pagination.conversation.message_list'));

        $this->get('final_work.seo_page')->setTitle($conversation->getTitle());

        return $this->render($this->ajaxOrNormalFolder($request, '@FinalWork/conversation/last_message.html.twig'), [
            'conversation' => $conversation,
            'conversationMessages' => $conversationMessages
        ]);
    }
}
