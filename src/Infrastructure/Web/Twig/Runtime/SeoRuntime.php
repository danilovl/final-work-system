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

namespace App\Infrastructure\Web\Twig\Runtime;

use App\Infrastructure\Service\SeoPageService;
use Danilovl\RenderServiceTwigExtensionBundle\Attribute\AsTwigFunction;

#[AsTwigFunction('seo_')]
class SeoRuntime
{
    private const string TITLE = '<title>%s</title>';

    private const string META = '<meta %s="%s" />';

    private const string META_CONTENT = '<meta %s="%s" content="%s" />';

    public function __construct(private readonly SeoPageService $seoPageService) {}

    public function setTitle(string $title): void
    {
        $this->seoPageService->setTitle($title);
    }

    public function title(): ?string
    {
        if ($this->seoPageService->getTitle() === null) {
            return null;
        }

        return sprintf(self::TITLE, $this->stripTags($this->seoPageService->getTransTitle()));
    }

    public function getMetaData(): string
    {
        $html = '';
        foreach ($this->seoPageService->getMetas() as $type => $metas) {
            foreach ($metas as $name => $meta) {
                [$content] = $meta;

                if (!empty($content)) {
                    $html .= sprintf(self::META_CONTENT . "\n",
                        $type,
                        $this->normalize($name),
                        $this->normalize($content)
                    );
                } else {
                    $html .= sprintf(self::META . "\n", $type, $this->normalize($name));
                }
            }
        }

        return $html;
    }

    private function normalize(string $string): string
    {
        return htmlentities(strip_tags($string), ENT_COMPAT, 'UTF-8');
    }

    private function stripTags(?string $string): ?string
    {
        return $string !== null ? strip_tags($string) : null;
    }
}
