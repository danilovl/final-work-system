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

namespace App\Domain\Event\Http;

use App\Application\Interfaces\Bus\CommandBusInterface;
use App\Infrastructure\Service\RequestService;
use App\Domain\Event\Bus\Command\DeleteEvent\DeleteEventCommand;
use App\Domain\Event\Entity\Event;
use App\Infrastructure\Web\Form\Factory\FormDeleteFactory;
use Danilovl\HashidsBundle\Interfaces\HashidsServiceInterface;
use Symfony\Component\HttpFoundation\{
    RedirectResponse,
    Request
};

readonly class EventDeleteHandle
{
    public function __construct(
        private RequestService $requestService,
        private FormDeleteFactory $formDeleteFactory,
        private HashidsServiceInterface $hashidsService,
        private CommandBusInterface $commandBus
    ) {}

    public function __invoke(Request $request, Event $event): RedirectResponse
    {
        $form = $this->formDeleteFactory
            ->createDeleteForm($event, 'event_schedule_delete')
            ->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $command = DeleteEventCommand::create($event);
                $this->commandBus->dispatch($command);

                return $this->requestService->redirectToRoute('event_calendar_manage');
            }

            return $this->requestService->redirectToRoute('event_detail', [
                'id' => $this->hashidsService->encode($event->getId())
            ]);
        }

        return $this->requestService->redirectToRoute('event_calendar_manage');
    }
}
