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

use App\Infrastructure\Interfaces\Bus\CommandBusInterface;
use App\Domain\Conversation\Bus\Command\CreateWorkConversation\CreateWorkConversationCommand;
use App\Domain\Conversation\Entity\Conversation;
use App\Infrastructure\Constant\FlashTypeConstant;
use App\Infrastructure\Service\RequestService;
use App\Infrastructure\Web\Form\Factory\FormActionFactory;
use App\Domain\Conversation\Service\{
    ConversationService,
    ConversationVariationService
};
use App\Domain\ConversationType\Constant\ConversationTypeConstant;
use App\Domain\User\Entity\User;
use App\Domain\Work\Entity\Work;
use Danilovl\HashidsBundle\Interfaces\HashidsServiceInterface;
use Symfony\Component\HttpFoundation\{
    Request,
    RedirectResponse
};
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

readonly class ConversationCreateWorkHandle
{
    public function __construct(
        private RequestService $requestService,
        private HashidsServiceInterface $hashidsService,
        private ConversationService $conversationService,
        private ConversationVariationService $conversationVariationService,
        private CommandBusInterface $commandBus,
        private FormActionFactory $formActionFactory
    ) {}

    public function __invoke(
        Request $request,
        Work $work,
        User $userOne,
        User $userTwo
    ): RedirectResponse {
        $isValidActionForm = $this->formActionFactory->isSubmittedAndValid($request);
        if (!$isValidActionForm) {
            throw new AccessDeniedHttpException('Invalid CSRF token.');
        }

        $isCheck = $this->conversationVariationService->checker($work, $userOne, $userTwo);

        if ($isCheck) {
            $workConversation = $this->conversationService
                ->checkWorkUsersConversation($work, $userOne, $userTwo);

            if ($workConversation === null) {
                $command = CreateWorkConversationCommand::create(
                    userOne: $userOne,
                    userTwo: $userTwo,
                    type: ConversationTypeConstant::WORK->value,
                    work: $work
                );
                /** @var Conversation $conversation */
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
