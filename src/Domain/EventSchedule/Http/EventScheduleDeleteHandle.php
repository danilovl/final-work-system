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
use App\Application\Form\Factory\FormDeleteFactory;
use App\Application\Service\RequestService;
use App\Domain\EventSchedule\Command\DeleteEventSchedule\DeleteEventScheduleCommand;
use App\Domain\EventSchedule\Entity\EventSchedule;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\HttpFoundation\{
    RedirectResponse,
    Request
};

readonly class EventScheduleDeleteHandle
{
    public function __construct(
        private RequestService $requestService,
        private MessageBusInterface $commandBus,
        private FormDeleteFactory $formDeleteFactory
    ) {}

    public function __invoke(Request $request, EventSchedule $eventSchedule): RedirectResponse
    {
        $form = $this->formDeleteFactory
            ->createDeleteForm($eventSchedule, 'event_schedule_delete')
            ->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $command = DeleteEventScheduleCommand::create($eventSchedule);
                $this->commandBus->dispatch($command);

                $this->requestService->addFlashTrans(FlashTypeConstant::SUCCESS->value, 'app.flash.form.delete.success');

                return $this->requestService->redirectToRoute('event_schedule_list');
            }

            $this->requestService->addFlashTrans(FlashTypeConstant::WARNING->value, 'app.flash.form.delete.warning');
            $this->requestService->addFlashTrans(FlashTypeConstant::ERROR->value, 'app.flash.form.delete.error');
        }

        return $this->requestService->redirectToRoute('event_schedule_list');
    }
}
