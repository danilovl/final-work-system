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

namespace App\Domain\Work\Http\Ajax;

use App\Application\Constant\AjaxJsonTypeConstant;
use App\Application\Helper\FormValidationMessageHelper;
use App\Application\Interfaces\Bus\CommandBusInterface;
use App\Application\Service\RequestService;
use App\Domain\User\Service\UserService;
use App\Domain\Work\Bus\Command\CreateWork\CreateWorkCommand;
use App\Domain\Work\Entity\Work;
use App\Domain\Work\EventDispatcher\WorkEventDispatcher;
use App\Domain\Work\Form\WorkForm;
use App\Domain\Work\Model\WorkModel;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\{
    JsonResponse,
    Request
};

readonly class WorkCreateHandle
{
    public function __construct(
        private RequestService $requestService,
        private UserService $userService,
        private FormFactoryInterface $formFactory,
        private WorkEventDispatcher $workEventDispatcher,
        private CommandBusInterface $commandBus
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        $user = $this->userService->getUser();

        $workModel = new WorkModel;
        $workModel->supervisor = $user;

        $form = $this->formFactory
            ->create(WorkForm::class, $workModel, ['user' => $user])
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $createWorkCommand = CreateWorkCommand::create($workModel);
            /** @var Work $work */
            $work = $this->commandBus->dispatchResult($createWorkCommand);

            $this->workEventDispatcher->onWorkCreate($work);

            return $this->requestService->createAjaxJson(AjaxJsonTypeConstant::CREATE_SUCCESS);
        }

        return $this->requestService->createAjaxJson(AjaxJsonTypeConstant::CREATE_FAILURE, [
            'data' => FormValidationMessageHelper::getErrorMessages($form)
        ]);
    }
}
