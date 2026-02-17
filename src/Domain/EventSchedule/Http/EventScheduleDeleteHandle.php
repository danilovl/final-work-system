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

use App\Infrastructure\Service\RequestService;
use App\Domain\EventSchedule\Command\DeleteEventSchedule\DeleteEventScheduleCommand;
use App\Domain\EventSchedule\Entity\EventSchedule;
use App\Infrastructure\Web\Form\Factory\FormDeleteFactory;
use Symfony\Component\HttpFoundation\{
    RedirectResponse,
    Request
};
use Symfony\Component\Messenger\MessageBusInterface;

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

        if ($form->isSubmitted() && $form->isValid()) {
            $command = DeleteEventScheduleCommand::create($eventSchedule);
            $this->commandBus->dispatch($command);

            return $this->requestService->redirectToRoute('event_schedule_list');
        }

        return $this->requestService->redirectToRoute('event_schedule_list');
    }
}
