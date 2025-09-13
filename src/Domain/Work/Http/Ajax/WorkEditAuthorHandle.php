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
use App\Domain\User\Form\UserEditForm;
use App\Domain\User\Model\UserModel;
use App\Domain\Work\Bus\Command\EditAuthor\EditAuthorCommand;
use App\Domain\Work\Entity\Work;
use App\Domain\Work\EventDispatcher\WorkEventDispatcher;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\{
    JsonResponse,
    Request
};

readonly class WorkEditAuthorHandle
{
    public function __construct(
        private RequestService $requestService,
        private FormFactoryInterface $formFactory,
        private WorkEventDispatcher $workEventDispatcher,
        private CommandBusInterface $commandBus
    ) {}

    public function __invoke(Request $request, Work $work): JsonResponse
    {
        $author = $work->getAuthor();
        $userModel = UserModel::fromUser($author);

        $form = $this->formFactory
            ->create(UserEditForm::class, $userModel)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $command = EditAuthorCommand::create($author, $userModel);
            $this->commandBus->dispatch($command);

            $this->workEventDispatcher->onWorkEditAuthor($work);

            return $this->requestService->createAjaxJson(AjaxJsonTypeConstant::SAVE_SUCCESS);
        }

        return $this->requestService->createAjaxJson(AjaxJsonTypeConstant::SAVE_FAILURE, [
            'data' => FormValidationMessageHelper::getErrorMessages($form)
        ]);
    }
}
