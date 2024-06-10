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

namespace App\Domain\Conversation\Http;

use App\Application\Constant\{
    FlashTypeConstant
};
use App\Application\Service\RequestService;
use App\Domain\Conversation\Factory\ConversationFactory;
use App\Domain\Conversation\Service\{
    ConversationService
};
use App\Domain\Conversation\Service\ConversationVariationService;
use App\Domain\ConversationType\Constant\ConversationTypeConstant;
use App\Domain\User\Entity\User;
use App\Domain\Work\Entity\Work;
use Danilovl\HashidsBundle\Interfaces\HashidsServiceInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

readonly class ConversationCreateWorkHandle
{
    public function __construct(
        private RequestService $requestService,
        private HashidsServiceInterface $hashidsService,
        private ConversationService $conversationService,
        private ConversationVariationService $conversationVariationService,
        private ConversationFactory $conversationFactory
    ) {}

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
                    ConversationTypeConstant::WORK->value,
                    $work
                );

                $this->conversationFactory->createConversationParticipant($conversation, [$userOne, $userTwo]);

                $this->requestService->addFlashTrans(FlashTypeConstant::SUCCESS->value, 'app.flash.form.create.success');

                return $this->requestService->redirectToRoute('conversation_detail', [
                    'id' => $this->hashidsService->encode($conversation->getId())
                ]);
            }

            return $this->requestService->redirectToRoute('conversation_detail', [
                'id' => $this->hashidsService->encode($workConversation->getId())
            ]);
        }

        $this->requestService->addFlashTrans(FlashTypeConstant::WARNING->value, 'app.flash.form.create.warning');
        $this->requestService->addFlashTrans(FlashTypeConstant::ERROR->value, 'app.flash.form.create.error');

        return $this->requestService->redirectToRoute('work_detail', [
            'id' => $this->hashidsService->encode($work->getId())
        ]);
    }
}
