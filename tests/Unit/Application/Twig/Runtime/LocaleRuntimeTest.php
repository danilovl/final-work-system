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

namespace App\Tests\Unit\Application\Twig\Runtime;

use App\Application\Constant\LocaleConstant;
use App\Application\Twig\Runtime\LocaleRuntime;
use PHPUnit\Framework\TestCase;

class LocaleRuntimeTest extends TestCase
{
    public function testGetLocales(): void
    {
        $locales = implode('|', LocaleConstant::values());
        $localeRuntime = new LocaleRuntime($locales);

        $expected = [
            [
                'code' => 'cs',
                'name' => 'čeština'
            ],
            [
                'code' => 'en',
                'name' => 'English'
            ],
            [
                'code' => 'ru',
                'name' => 'русский'
            ]
        ];

        $this->assertEquals($expected, $localeRuntime->getLocales());
    }
}
