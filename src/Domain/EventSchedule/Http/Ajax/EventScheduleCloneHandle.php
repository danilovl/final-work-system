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
use App\Application\Service\RequestService;
use App\Domain\EventSchedule\Entity\EventSchedule;
use App\Domain\EventSchedule\EventScheduleCloneModel;
use App\Domain\EventSchedule\Factory\EventScheduleFactory;
use App\Domain\EventSchedule\Form\EventScheduleCloneForm;
use App\Domain\User\Service\UserService;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\{
    Request,
    JsonResponse
};

readonly class EventScheduleCloneHandle
{
    public function __construct(
        private RequestService $requestService,
        private UserService $userService,
        private EventScheduleFactory $eventScheduleFactory,
        private FormFactoryInterface $formFactory
    ) {}

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
