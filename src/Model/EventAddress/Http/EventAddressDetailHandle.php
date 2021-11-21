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

namespace App\Model\EventAddress\Http;

use App\Model\EventAddress\Entity\EventAddress;
use App\Form\Factory\FormDeleteFactory;
use App\Service\{
    SeoPageService,
    TwigRenderService
};
use Symfony\Component\HttpFoundation\Response;

class EventAddressDetailHandle
{
    public function __construct(
        private TwigRenderService $twigRenderService,
        private SeoPageService $seoPageService,
        private FormDeleteFactory $formDeleteFactory
    ) {
    }

    public function handle(EventAddress $eventAddress): Response
    {
        $this->seoPageService->setTitle($eventAddress->getName());

        $deleteForm = $this->formDeleteFactory
            ->createDeleteForm($eventAddress, 'event_address_delete')
            ->createView();

        return $this->twigRenderService->render('event_address/detail.html.twig', [
            'eventAddress' => $eventAddress,
            'deleteForm' => $deleteForm
        ]);
    }
}
