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

namespace App\Application\Twig;

use App\Application\Twig\Runtime\SeoRuntime;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class SeoExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('set_seo_title', [SeoRuntime::class, 'setTitle'], ['is_safe' => ['html']]),
            new TwigFunction('seo_title', [SeoRuntime::class, 'getTitle'], ['is_safe' => ['html']]),
            new TwigFunction('seo_meta_data', [SeoRuntime::class, 'getMetaData'], ['is_safe' => ['html']])
        ];
    }
}
