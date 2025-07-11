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

use App\Infrastructure\Service\{
    RequestService,
    SeoPageService,
    TwigRenderService
};
use App\Domain\EventSchedule\Entity\EventSchedule;
use App\Domain\EventSchedule\Factory\EventScheduleFactory;
use App\Domain\EventSchedule\Form\EventScheduleForm;
use App\Domain\EventSchedule\Model\EventScheduleModel;
use App\Domain\User\Service\UserService;
use Danilovl\HashidsBundle\Interfaces\HashidsServiceInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\{
    Request,
    Response
};

readonly class EventScheduleEditHandle
{
    public function __construct(
        private RequestService $requestService,
        private UserService $userService,
        private TwigRenderService $twigRenderService,
        private HashidsServiceInterface $hashidsService,
        private EventScheduleFactory $eventScheduleFactory,
        private FormFactoryInterface $formFactory,
        private SeoPageService $seoPageService
    ) {}

    public function __invoke(Request $request, EventSchedule $eventSchedule): Response
    {
        $user = $this->userService->getUser();

        $eventScheduleModel = EventScheduleModel::fromEventSchedule($eventSchedule);
        $form = $this->formFactory
            ->create(EventScheduleForm::class, $eventScheduleModel, [
                'addresses' => $user->getEventAddressOwner()
            ])
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->eventScheduleFactory->flushFromModel($eventScheduleModel, $eventSchedule);

            return $this->requestService->redirectToRoute('event_schedule_detail', [
                'id' => $this->hashidsService->encode($eventSchedule->getId())
            ]);
        }

        $this->seoPageService->setTitle($eventSchedule->getName());

        return $this->twigRenderService->renderToResponse('domain/event_schedule/edit.html.twig', [
            'eventSchedule' => $eventSchedule,
            'form' => $form->createView()
        ]);
    }
}
