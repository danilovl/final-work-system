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

namespace App\Model\EventSchedule\Http\Ajax;

use App\Constant\AjaxJsonTypeConstant;
use App\Model\EventSchedule\Entity\EventSchedule;
use App\Helper\FormValidationMessageHelper;
use App\Model\EventSchedule\EventScheduleCloneModel;
use App\Model\EventSchedule\Factory\EventScheduleFactory;
use App\Model\EventSchedule\Form\EventScheduleCloneForm;
use Symfony\Component\Form\FormFactoryInterface;
use App\Service\{
    UserService,
    RequestService
};
use Symfony\Component\HttpFoundation\{
    Request,
    JsonResponse
};

class EventScheduleCloneHandle
{
    public function __construct(
        private RequestService $requestService,
        private UserService $userService,
        private EventScheduleFactory $eventScheduleFactory,
        private FormFactoryInterface $formFactory
    ) {
    }

    public function handle(Request $request, EventSchedule $eventSchedule): JsonResponse
    {
        $eventScheduleCloneModel = new EventScheduleCloneModel;
        $form = $this->formFactory
            ->create(EventScheduleCloneForm::class, $eventScheduleCloneModel)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->eventScheduleFactory->cloneEventSchedule(
                $this->userService->getUser(),
                $eventSchedule,
                $eventScheduleCloneModel->start
            );

            return $this->requestService->createAjaxJson(AjaxJsonTypeConstant::SAVE_SUCCESS);
        }

        return $this->requestService->createAjaxJson(AjaxJsonTypeConstant::SAVE_FAILURE, [
            'data' => FormValidationMessageHelper::getErrorMessages($form)
        ]);
    }
}
