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

namespace App\Tests\Unit\Translations;

use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Yaml;

class TranslationKeyParityTest extends TestCase
{
    private const string TRANSLATIONS_DIR = __DIR__ . '/../../../translations';

    private const array LOCALES = ['cs', 'en', 'ru'];

    #[DataProvider('provideDomainHasSameKeysInAllLocalesCases')]
    public function testDomainHasSameKeysInAllLocales(string $domain): void
    {
        $keysByLocale = [];
        foreach (self::LOCALES as $locale) {
            $file = sprintf('%s/%s.%s.yaml', self::TRANSLATIONS_DIR, $domain, $locale);
            $this->assertFileExists($file);

            /** @var array<string, mixed> $translations */
            $translations = Yaml::parseFile($file) ?? [];
            $keysByLocale[$locale] = $this->flattenKeys($translations);
        }

        $allKeys = array_unique(array_merge(...array_values($keysByLocale)));
        sort($allKeys);

        foreach ($keysByLocale as $locale => $keys) {
            $missingKeys = array_values(array_diff($allKeys, $keys));
            $message = sprintf('Locale "%s" of domain "%s" misses keys.', $locale, $domain);

            $this->assertSame([], $missingKeys, $message);
        }
    }

    public static function provideDomainHasSameKeysInAllLocalesCases(): Generator
    {
        $pattern = sprintf('%s/*.yaml', self::TRANSLATIONS_DIR);
        $files = glob($pattern) ?: [];
        $domains = [];

        foreach ($files as $file) {
            $fileName = basename($file, '.yaml');
            $domain = explode('.', $fileName)[0];
            $domains[$domain] = $domain;
        }

        foreach ($domains as $domain) {
            yield $domain => [
                'domain' => $domain
            ];
        }
    }

    /**
     * @param array<string, mixed> $translations
     * @return string[]
     */
    private function flattenKeys(array $translations, string $prefix = ''): array
    {
        $keys = [];

        foreach ($translations as $key => $value) {
            $fullKey = $prefix === '' ? (string) $key : $prefix . '.' . $key;

            if (is_array($value)) {
                /** @var array<string, mixed> $value */
                $nestedKeys = $this->flattenKeys($value, $fullKey);
                array_push($keys, ...$nestedKeys);

                continue;
            }

            $keys[] = $fullKey;
        }

        return $keys;
    }
}
