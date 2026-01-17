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

use App\Infrastructure\Service\{
    SeoPageService,
    TwigRenderService
};
use App\Domain\EventAddress\Entity\EventAddress;
use App\Infrastructure\Web\Form\Factory\FormDeleteFactory;
use Symfony\Component\HttpFoundation\Response;

readonly class EventAddressDetailHandle
{
    public function __construct(
        private TwigRenderService $twigRenderService,
        private SeoPageService $seoPageService,
        private FormDeleteFactory $formDeleteFactory
    ) {}

    public function __invoke(EventAddress $eventAddress): Response
    {
        $this->seoPageService->setTitle($eventAddress->getName());

        $deleteForm = $this->formDeleteFactory
            ->createDeleteForm($eventAddress, 'event_address_delete')
            ->createView();

        return $this->twigRenderService->renderToResponse('domain/event_address/detail.html.twig', [
            'eventAddress' => $eventAddress,
            'deleteForm' => $deleteForm
        ]);
    }
}
