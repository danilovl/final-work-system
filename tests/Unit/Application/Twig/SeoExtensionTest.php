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

namespace App\Tests\Unit\Application\Twig;

use App\Application\Twig\SeoExtension;
use PHPUnit\Framework\TestCase;
use Twig\TwigFunction;

class SeoExtensionTest extends TestCase
{
    public function testGetFunctions(): void
    {
        $seoExtension = new SeoExtension;
        $twigFunction = array_map(static function (TwigFunction $twigFunction): string {
            return $twigFunction->getName();
        }, $seoExtension->getFunctions());

        $this->assertEquals(
            ['set_seo_title', 'seo_title', 'seo_meta_data'],
            $twigFunction
        );
    }
}
