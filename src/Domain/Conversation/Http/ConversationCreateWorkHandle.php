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

use App\Application\Interfaces\Bus\CommandBusInterface;
use App\Domain\Conversation\Bus\Command\CreateWorkConversation\CreateWorkConversationCommand;
use App\Domain\Conversation\Entity\Conversation;
use App\Application\Constant\{
    FlashTypeConstant
};
use App\Application\Service\RequestService;
use App\Domain\Conversation\Service\{
    ConversationService,
    ConversationVariationService
};
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
        private CommandBusInterface $commandBus
    ) {}

    public function __invoke(
        Work $work,
        User $userOne,
        User $userTwo
    ): RedirectResponse {
        $isCheck = $this->conversationVariationService->checker($work, $userOne, $userTwo);

        if ($isCheck) {
            $workConversation = $this->conversationService
                ->checkWorkUsersConversation($work, $userOne, $userTwo);

            if ($workConversation === null) {
                $command = CreateWorkConversationCommand::create($userOne, $userTwo, ConversationTypeConstant::WORK->value, $work);
                /** @var Conversation $eventComment */
                $conversation = $this->commandBus->dispatchResult($command);

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
