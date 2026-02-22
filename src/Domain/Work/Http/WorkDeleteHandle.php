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

namespace App\Domain\Work\Http;

use App\Application\Interfaces\Bus\CommandBusInterface;
use App\Infrastructure\Service\RequestService;
use App\Domain\Work\Bus\Command\DeleteWork\DeleteWorkCommand;
use App\Domain\Work\Constant\WorkUserTypeConstant;
use App\Domain\Work\Entity\Work;
use App\Infrastructure\Web\Form\Factory\FormDeleteFactory;
use Danilovl\HashidsBundle\Interfaces\HashidsServiceInterface;
use Symfony\Component\HttpFoundation\{
    RedirectResponse,
    Request
};

readonly class WorkDeleteHandle
{
    public function __construct(
        private RequestService $requestService,
        private HashidsServiceInterface $hashidsService,
        private FormDeleteFactory $formDeleteFactory,
        private CommandBusInterface $commandBus
    ) {}

    public function __invoke(Request $request, Work $work): RedirectResponse
    {
        $form = $this->formDeleteFactory
            ->createDeleteForm($work, 'work_delete')
            ->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $command = DeleteWorkCommand::create($work);
                $this->commandBus->dispatch($command);

                return $this->requestService->redirectToRoute('work_list', [
                    'type' => WorkUserTypeConstant::SUPERVISOR->value
                ]);
            }

            return $this->requestService->redirectToRoute('work_detail', [
                'id' => $this->hashidsService->encode($work->getId())
            ]);
        }

        return $this->requestService->redirectToRoute('work_list', [
            'type' => WorkUserTypeConstant::SUPERVISOR->value
        ]);
    }
}
