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

namespace FinalWork\FinalWorkBundle\Services\Seo;

use FinalWork\FinalWorkBundle\Constant\TranslationConstant;
use FinalWork\FinalWorkBundle\Exception\RuntimeException;
use Symfony\Component\Translation\TranslatorInterface;

class SeoPageService implements SeoPageInterface
{
    /**
     * @var string|null
     */
    protected $title;

    /**
     * @var array
     */
    protected $metas;

    /**
     * @var string
     */
    protected $separator = ' ';

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * DateService constructor.
     * @param TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
        $this->metas = [
            'http-equiv' => [],
            'name' => [],
            'schema' => [],
            'charset' => [],
            'property' => [],
        ];
    }

    /**
     * @param string|null $title
     * @return $this
     */
    public function setTitle(?string $title): SeoPageInterface
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @param string $title
     * @param string|null $separator
     * @return $this
     */
    public function addTitle(string $title, string $separator = null): SeoPageInterface
    {
        $separator = $separator ?? $this->separator;
        $this->title = $this->getTransTitle() . $separator . $this->getTransTitle($title);

        return $this;
    }

    /**
     * @return string|null
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * @param string|null $title
     * @return string|null
     */
    public function getTransTitle(string $title = null): ?string
    {
        $title = $title ?? $this->title;

        if (strpos($title, TranslationConstant::DEFAULT_START_KEY) !== false) {
            return $this->translator->trans($title);
        }

        return $title;
    }

    /**
     * @return array
     */
    public function getMetas(): array
    {
        return $this->metas;
    }

    /**
     * @param string $type
     * @param string $name
     * @param string $content
     * @param array $extras
     * @return $this
     */
    public function addMeta(
        string $type,
        string $name,
        string $content,
        array $extras = []
    ): SeoPageInterface {
        if (!isset($this->metas[$type])) {
            $this->metas[$type] = [];
        }

        $this->metas[$type][$name] = [$content, $extras];

        return $this;
    }

    /**
     * @param string $type
     * @param string $name
     * @return bool
     */
    public function hasMeta(string $type, string $name): bool
    {
        return isset($this->metas[$type][$name]);
    }

    /**
     * @param array $metaData
     * @return $this
     */
    public function setMetas(array $metaData): SeoPageInterface
    {
        $this->metas = [];

        foreach ($metaData as $type => $metas) {
            if (!is_array($metas)) {
                throw new RuntimeException('$metas must be an array');
            }

            foreach ($metas as $name => $meta) {
                [$content, $extras] = $this->normalize($meta);

                $this->addMeta($type, $name, $content, $extras);
            }
        }

        return $this;
    }

    /**
     * @param mixed $meta
     * @return array
     */
    private function normalize($meta): array
    {
        if (is_string($meta)) {
            return [$meta, []];
        }

        return $meta;
    }
}
