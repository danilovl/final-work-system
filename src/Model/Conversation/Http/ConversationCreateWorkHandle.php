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

namespace App\Model\Conversation\Http;

use App\Constant\ConversationTypeConstant;
use App\Model\Conversation\Factory\ConversationFactory;
use App\Model\Conversation\Service\{
    ConversationService,
    ConversationVariationService
};
use App\Entity\{
    User,
    Work
};
use Danilovl\HashidsBundle\Interfaces\HashidsServiceInterface;
use App\Constant\FlashTypeConstant;
use App\Service\RequestService;
use Symfony\Component\HttpFoundation\RedirectResponse;

class ConversationCreateWorkHandle
{
    public function __construct(
        private RequestService $requestService,
        private HashidsServiceInterface $hashidsService,
        private ConversationService $conversationService,
        private ConversationVariationService $conversationVariationService,
        private ConversationFactory $conversationFactory
    ) {
    }

    public function handle(
        Work $work,
        User $userOne,
        User $userTwo
    ): RedirectResponse {
        $isCheck = $this->conversationVariationService->checker($work, $userOne, $userTwo);

        if ($isCheck) {
            $workConversation = $this->conversationService
                ->checkWorkUsersConversation($work, $userOne, $userTwo);

            if ($workConversation === null) {
                $conversation = $this->conversationFactory->createConversation(
                    $userOne,
                    ConversationTypeConstant::WORK,
                    $work
                );

                $this->conversationFactory->createConversationParticipant($conversation, [$userOne, $userTwo]);

                $this->requestService->addFlashTrans(FlashTypeConstant::SUCCESS, 'app.flash.form.create.success');

                return $this->requestService->redirectToRoute('conversation_detail', [
                    'id' => $this->hashidsService->encode($conversation->getId())
                ]);
            }

            return $this->requestService->redirectToRoute('conversation_detail', [
                'id' => $this->hashidsService->encode($workConversation->getId())
            ]);
        }

        $this->requestService->addFlashTrans(FlashTypeConstant::WARNING, 'app.flash.form.create.warning');
        $this->requestService->addFlashTrans(FlashTypeConstant::ERROR, 'app.flash.form.create.error');

        return $this->requestService->redirectToRoute('work_detail', [
            'id' => $this->hashidsService->encode($work->getId())
        ]);
    }
}
