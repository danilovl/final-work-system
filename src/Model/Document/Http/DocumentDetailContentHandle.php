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

namespace App\Model\Document\Http;

use App\Entity\Media;
use App\Service\TwigRenderService;
use Symfony\Component\HttpFoundation\Response;

class DocumentDetailContentHandle
{
    public function __construct(private TwigRenderService $twigRenderService)
    {
    }

    public function handle(Media $media): Response
    {
        return $this->twigRenderService->render('document/detail_content.html.twig', [
            'document' => $media
        ]);
    }
}
