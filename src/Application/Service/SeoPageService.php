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

namespace App\Application\Service;

use App\Application\Constant\TranslationConstant;
use App\Application\Exception\RuntimeException;
use Symfony\Contracts\Translation\TranslatorInterface;

class SeoPageService
{
    final public const string DEFAULT_SEPARATOR = '';

    private ?string $title = null;
    private array $metas;
    private string $separator;

    public function __construct(private readonly TranslatorInterface $translator)
    {
        $this->separator = self::DEFAULT_SEPARATOR;
        $this->metas = [
            'http-equiv' => [],
            'name' => [],
            'schema' => [],
            'charset' => [],
            'property' => [],
        ];
    }

    public function setTitle(?string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function addTitle(string $title, string $separator = null): self
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

        if ($title !== null && str_contains($title, TranslationConstant::DEFAULT_START_KEY->value)) {
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
    ): self {
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

    public function setMetas(array $metaData): self
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

    private function normalize(string|array $meta): array
    {
        return is_string($meta) ? [$meta, []] : $meta;
    }
}
