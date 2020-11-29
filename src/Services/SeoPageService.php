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

namespace App\Services;

use App\Constant\TranslationConstant;
use App\Exception\RuntimeException;
use App\Services\Interfaces\SeoPageInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class SeoPageService implements SeoPageInterface
{
    public const DEFAULT_SEPARATOR = '';

    private ?string $title = null;
    private array $metas;
    private string $separator;
    private TranslatorInterface $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
        $this->separator = self::DEFAULT_SEPARATOR;
        $this->metas = [
            'http-equiv' => [],
            'name' => [],
            'schema' => [],
            'charset' => [],
            'property' => [],
        ];
    }

    public function setTitle(?string $title): SeoPageInterface
    {
        $this->title = $title;

        return $this;
    }

    public function addTitle(string $title, string $separator = null): SeoPageInterface
    {
        $separator = $separator ?? $this->separator;
        $this->title = $this->getTransTitle() . $separator . $this->getTransTitle($title);

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function getTransTitle(string $title = null): ?string
    {
        $title = $title ?? $this->title;

        if (strpos($title, TranslationConstant::DEFAULT_START_KEY) !== false) {
            return $this->translator->trans($title);
        }

        return $title;
    }

    public function getMetas(): array
    {
        return $this->metas;
    }

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

    public function hasMeta(string $type, string $name): bool
    {
        return isset($this->metas[$type][$name]);
    }

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

    private function normalize($meta): array
    {
        return is_string($meta) ? [$meta, []] : $meta;
    }
}
