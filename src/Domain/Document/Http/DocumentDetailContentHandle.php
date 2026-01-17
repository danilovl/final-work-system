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

namespace App\Domain\Document\Http;

use App\Infrastructure\Service\TwigRenderService;
use App\Domain\Media\Entity\Media;
use Symfony\Component\HttpFoundation\Response;

readonly class DocumentDetailContentHandle
{
    public function __construct(private TwigRenderService $twigRenderService) {}

    public function __invoke(Media $media): Response
    {
        return $this->twigRenderService->renderToResponse('domain/document/detail_content.html.twig', [
            'document' => $media
        ]);
    }
}
