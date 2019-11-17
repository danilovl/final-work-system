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

namespace FinalWork\FinalWorkBundle\Twig;

use FinalWork\FinalWorkBundle\Services\Seo\SeoPageInterface;
use Twig\Extension\AbstractExtension;

class SeoRuntime extends AbstractExtension
{
    private const TITLE = '<title>%s</title>';
    private const META = '<meta %s="%s" />';
    private const META_CONTENT = '<meta %s="%s" content="%s" />';

    /**
     * @var SeoPageInterface
     */
    protected $page;

    /**
     * @param SeoPageInterface $page
     */
    public function __construct(SeoPageInterface $page)
    {
        $this->page = $page;
    }

    /**
     * @param string $title
     */
    public function setTitle(string $title): void
    {
        $this->page->setTitle($title);
    }

    /**
     * @return string|null
     */
    public function getTitle(): ?string
    {
        if ($this->page->getTitle() === null) {
            return null;
        }

        return sprintf(self::TITLE, $this->stripTags($this->page->getTransTitle()));
    }

    /**
     * @return string
     */
    public function getMetaData(): string
    {
        $html = '';
        foreach ($this->page->getMetas() as $type => $metas) {
            foreach ((array)$metas as $name => $meta) {
                [$content, $extras] = $meta;

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

    /**
     * @param string $string
     * @return string
     */
    private function normalize(string $string): string
    {
        return htmlentities(strip_tags($string), ENT_COMPAT, 'UTF-8');
    }

    /**
     * @param string|null $string
     * @return string|null
     */
    private function stripTags(?string $string): ?string
    {
        return $string !== null ? strip_tags($string) : null;
    }
}
