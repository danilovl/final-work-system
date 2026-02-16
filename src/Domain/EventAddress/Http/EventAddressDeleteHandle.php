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

namespace App\Domain\EventAddress\Http;

use App\Infrastructure\Service\RequestService;
use App\Domain\EventAddress\Bus\Command\DeleteEventAddress\DeleteEventAddressCommand;
use App\Domain\EventAddress\Entity\EventAddress;
use App\Infrastructure\Web\Form\Factory\FormDeleteFactory;
use Symfony\Component\HttpFoundation\{
    RedirectResponse,
    Request
};
use Symfony\Component\Messenger\MessageBusInterface;

readonly class EventAddressDeleteHandle
{
    public function __construct(
        private RequestService $requestService,
        private MessageBusInterface $messageBus,
        private FormDeleteFactory $formDeleteFactory
    ) {}

    public function __invoke(Request $request, EventAddress $eventAddress): RedirectResponse
    {
        $form = $this->formDeleteFactory
            ->createDeleteForm($eventAddress, 'event_address_delete')
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $command = DeleteEventAddressCommand::create($eventAddress);
            $this->messageBus->dispatch($command);

            return $this->requestService->redirectToRoute('event_address_list');
        }

        return $this->requestService->redirectToRoute('event_address_list');
    }
}
