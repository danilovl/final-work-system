<?php declare(strict_types=1);

/*
 *
 * This file is part of the FinalWorkSystem project.
 * (c) Vladimir Danilov
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace App\Application\Util;

class TextHighlightWordUtil
{
    public static function highlightEntireWords(string $text, array $words): string
    {
        foreach ($words as $word) {
            $text = self::replaceWord($text, $word);
        }

        return $text;
    }

    public static function highlightPartWords(string $text, array $words): string
    {
        foreach ($words as $word) {
            $text = self::replaceWord($text, $word, false);
        }

        return $text;
    }

    private static function replaceWord(string $text, string $word, bool $entireWord = true): string
    {
        $highlightedWord = '<span style="background-color: yellow;">$0</span>';
        $pattern = sprintf('~%2$s(%1$s)%2$s~iu', preg_quote($word), $entireWord === true ? '\b' : '');
        /** @var string $text */
        $text = preg_replace($pattern, $highlightedWord, $text);

        return $text;
    }
}
