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

namespace App\Tests\Application\Util;

use App\Application\Util\TextHighlightWordUtil;
use Generator;
use PHPUnit\Framework\TestCase;

class TextHighlightWordTest extends TestCase
{
    /**
     * @dataProvider highlightEntireWordsDataProvider
     */
    public function testHighlightEntireWords(string $text, array $words, string $expectedResult): void
    {
        $highlightedText = TextHighlightWordUtil::highlightEntireWords($text, $words);

        $this->assertEquals($expectedResult, $highlightedText);
    }

    /**
     * @dataProvider highlightPartWordsDataProvider
     */
    public function testHighlightPartWords(string $text, array $words, string $expectedResult): void
    {
        $highlightedText = TextHighlightWordUtil::highlightPartWords($text, $words);

        $this->assertEquals($expectedResult, $highlightedText);
    }

    public function highlightEntireWordsDataProvider(): Generator
    {
        yield ['Lorem ipsum dolor sit amet', ['ipsum'], 'Lorem <span style="background-color: yellow;">ipsum</span> dolor sit amet'];
        yield ['Lorem ipsum dolor sit amet', ['Lorem', 'sit'], '<span style="background-color: yellow;">Lorem</span> ipsum dolor <span style="background-color: yellow;">sit</span> amet'];
        yield ['Lorem ipsum dolor sit amet', ['dolor', 'amet'], 'Lorem ipsum <span style="background-color: yellow;">dolor</span> sit <span style="background-color: yellow;">amet</span>'];
        yield ['Lorem ipsum Dolor sit Amet', ['dolor', 'amet'], 'Lorem ipsum <span style="background-color: yellow;">Dolor</span> sit <span style="background-color: yellow;">Amet</span>'];
        yield ['Lorem ipsum DOLOR sit AMET', ['dolor', 'amet'], 'Lorem ipsum <span style="background-color: yellow;">DOLOR</span> sit <span style="background-color: yellow;">AMET</span>'];
        yield ['která bude souviset s náplní mé prace v ŠA', ['ša', 'amet'], 'která bude souviset s náplní mé prace v <span style="background-color: yellow;">ŠA</span>'];
    }

    public function highlightPartWordsDataProvider(): Generator
    {
        yield ['Lorem ipsumdolor sit amet', ['ipsum'], 'Lorem <span style="background-color: yellow;">ipsum</span>dolor sit amet'];
        yield ['Lorem ipsum dolor sitamet', ['Lorem', 'sit'], '<span style="background-color: yellow;">Lorem</span> ipsum dolor <span style="background-color: yellow;">sit</span>amet'];
        yield ['Lorem ipsum dolor sit amet', ['dolor', 'amet'], 'Lorem ipsum <span style="background-color: yellow;">dolor</span> sit <span style="background-color: yellow;">amet</span>'];
        yield ['Lorem ipsum DOLOR sit AMET', ['dolor', 'amet'], 'Lorem ipsum <span style="background-color: yellow;">DOLOR</span> sit <span style="background-color: yellow;">AMET</span>'];
        yield ['která bude souviset s náplní mé prace v ŠA', ['ša', 'amet'], 'která bude souviset s náplní mé prace v <span style="background-color: yellow;">ŠA</span>'];
        yield ['odstareni systemovych bagu.', ['temovy'], 'odstareni sys<span style="background-color: yellow;">temovy</span>ch bagu.'];
    }
}
