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

namespace App\Twig\Runtime;

use App\Helper\RegexHelper;
use Symfony\Component\Routing\RouterInterface;
use Twig\Extension\AbstractExtension;

class AwayRuntime extends AbstractExtension
{
    public function __construct(
        private RouterInterface $router,
        private string $domain
    ) {
    }

    public function to(?string $text): ?string
    {
        if ($text === null) {
            return null;
        }

        $matches = RegexHelper::allLinks($text);
        if ($matches === null) {
            return $text;
        }

        foreach ($matches as $match) {
            $link = $match[0];
            $href = $match[1];

            $parseUrl = parse_url($href);
            $host = $parseUrl['host'] ?? null;
            if ($host === null || $host === $this->domain) {
                continue;
            }

            $externalUrlRedirectHref = $this->router->generate('away_to', ['url' => $href]);
            $externalUrlRedirectLink = str_replace($href, $externalUrlRedirectHref, $link);
            $text = str_replace($link, $externalUrlRedirectLink, $text);
        }

        return $text;
    }
}
