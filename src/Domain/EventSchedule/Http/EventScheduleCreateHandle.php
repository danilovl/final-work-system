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

namespace App\Domain\EventSchedule\Http;

use App\Application\Constant\FlashTypeConstant;
use App\Application\Service\{
    RequestService,
    TwigRenderService
};
use App\Domain\EventSchedule\Command\CreateEventSchedule\CreateEventScheduleCommand;
use App\Domain\EventSchedule\Form\EventScheduleForm;
use App\Domain\EventSchedule\Model\EventScheduleModel;
use App\Domain\User\Service\UserService;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\{
    Request,
    Response
};
use Symfony\Component\Messenger\MessageBusInterface;

readonly class EventScheduleCreateHandle
{
    public function __construct(
        private RequestService $requestService,
        private UserService $userService,
        private TwigRenderService $twigRenderService,
        private FormFactoryInterface $formFactory,
        private MessageBusInterface $messageBus
    ) {}

    public function __invoke(Request $request): Response
    {
        $user = $this->userService->getUser();

        $eventScheduleModel = new EventScheduleModel;
        $eventScheduleModel->owner = $user;

        $form = $this->formFactory
            ->create(EventScheduleForm::class, $eventScheduleModel, [
                'addresses' => $user->getEventAddressOwner()
            ])
            ->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $command = CreateEventScheduleCommand::create($eventScheduleModel);
                $this->messageBus->dispatch($command);

                $this->requestService->addFlashTrans(FlashTypeConstant::SUCCESS->value, 'app.flash.form.create.success');

                return $this->requestService->redirectToRoute('event_schedule_list');
            }

            $this->requestService->addFlashTrans(FlashTypeConstant::WARNING->value, 'app.flash.form.create.warning');
            $this->requestService->addFlashTrans(FlashTypeConstant::ERROR->value, 'app.flash.form.create.error');
        }

        return $this->twigRenderService->renderToResponse('domain/event_schedule/create.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
