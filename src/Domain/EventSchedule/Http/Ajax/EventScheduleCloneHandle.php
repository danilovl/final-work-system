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

namespace App\Domain\EventSchedule\Http\Ajax;

use App\Application\Constant\AjaxJsonTypeConstant;
use App\Application\Helper\FormValidationMessageHelper;
use App\Infrastructure\Service\RequestService;
use App\Domain\EventSchedule\Command\CloneEventSchedule\CloneEventScheduleCommand;
use App\Domain\EventSchedule\Entity\EventSchedule;
use App\Domain\EventSchedule\Form\EventScheduleCloneForm;
use App\Domain\EventSchedule\Model\EventScheduleCloneModel;
use App\Domain\User\Service\UserService;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\{
    Request,
    JsonResponse
};
use Symfony\Component\Messenger\MessageBusInterface;

readonly class EventScheduleCloneHandle
{
    public function __construct(
        private RequestService $requestService,
        private UserService $userService,
        private FormFactoryInterface $formFactory,
        private MessageBusInterface $messageBus
    ) {}

    public function __invoke(Request $request, EventSchedule $eventSchedule): JsonResponse
    {
        $eventScheduleCloneModel = new EventScheduleCloneModel;
        $form = $this->formFactory
            ->create(EventScheduleCloneForm::class, $eventScheduleCloneModel)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $command = CloneEventScheduleCommand::create(
                $this->userService->getUser(),
                $eventSchedule,
                $eventScheduleCloneModel
            );
            $this->messageBus->dispatch($command);

            return $this->requestService->createAjaxJson(AjaxJsonTypeConstant::SAVE_SUCCESS);
        }

        return $this->requestService->createAjaxJson(AjaxJsonTypeConstant::SAVE_FAILURE, [
            'data' => FormValidationMessageHelper::getErrorMessages($form)
        ]);
    }
}
