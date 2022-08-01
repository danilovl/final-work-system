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

namespace App\Domain\Version\Http;

use App\Application\Service\TwigRenderService;
use App\Domain\Media\Entity\Media;
use Symfony\Component\HttpFoundation\Response;

class VersionDetailContentHandle
{
    public function __construct(private readonly TwigRenderService $twigRenderService) {}

    public function handle(Media $media): Response
    {
        return $this->twigRenderService->render('version/detail_content.html.twig', [
            'version' => $media,
            'work' => $media->getWork()
        ]);
    }
}
