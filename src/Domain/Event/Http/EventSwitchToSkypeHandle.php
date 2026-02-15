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

use App\Application\Constant\FlashTypeConstant;
use App\Application\Interfaces\Bus\CommandBusInterface;
use App\Infrastructure\Service\RequestService;
use App\Domain\Event\Bus\Command\EventSwitchToSkype\EventSwitchToSkypeCommand;
use App\Domain\Event\Entity\Event;
use Danilovl\HashidsBundle\Interfaces\HashidsServiceInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

readonly class EventSwitchToSkypeHandle
{
    public function __construct(
        private RequestService $requestService,
        private HashidsServiceInterface $hashidsService,
        private CommandBusInterface $commandBus
    ) {}

    public function __invoke(Event $event): RedirectResponse
    {
        $command = EventSwitchToSkypeCommand::create($event);
        $updatedEvent = $this->commandBus->dispatchResult($command);

        if ($updatedEvent === null) {
            $this->requestService->addFlashTrans(FlashTypeConstant::WARNING->value, 'app.flash.form.save.warning');
            $this->requestService->addFlashTrans(FlashTypeConstant::ERROR->value, 'app.flash.form.save.error');
        }

        return $this->requestService->redirectToRoute('event_detail', [
            'id' => $this->hashidsService->encode($event->getId())
        ]);
    }
}
