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

namespace App\Infrastructure\Web\Twig\Extension;

use Override;
use Symfony\Bridge\Twig\Extension\HtmlSanitizerExtension as BaseHtmlSanitizerExtension;
use Symfony\Component\DependencyInjection\Attribute\{
    AsDecorator,
    AutowireDecorated
};
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

#[AsDecorator('twig.extension.htmlsanitizer')]
class HtmlSanitizerExtension extends AbstractExtension
{
    public function __construct(
        #[AutowireDecorated]
        private readonly BaseHtmlSanitizerExtension $htmlSanitizerExtension
    ) {}

    #[Override]
    public function getFilters(): array
    {
        return [
            new TwigFilter('sanitize_html', $this->sanitize(...), ['is_safe' => ['html']])
        ];
    }

    public function sanitize(?string $html, ?string $sanitizer = null): string
    {
        if ($html === null) {
            return '';
        }

        return $this->htmlSanitizerExtension->sanitize($html, $sanitizer);
    }
}
