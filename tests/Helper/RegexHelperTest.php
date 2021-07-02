<?php

namespace App\Tests\Helper;

use App\Helper\RegexHelper;
use Generator;
use PHPUnit\Framework\TestCase;

class RegexHelperTest extends TestCase
{
    /**
     * @dataProvider textProvider
     */
    public function testAllLinks(string $text, mixed $result): void
    {
        $this->assertEquals(RegexHelper::allLinks($text), $result);
    }

    public function textProvider(): Generator
    {
        yield [
            'Now the widgets are ready to go. The engine combines the entire tree into a <a href="www.w3.com">Visit W3</a>  render-able view and tells the operating system to display it. 
             This is called rasterizing, and it’sthe last step.
             <a href="https://www.w3schools.com">Visit W3Schools</a> ',
            [
                [
                    '<a href="www.w3.com">',
                    'www.w3.com',
                    ''
                ],
                [
                    '<a href="https://www.w3schools.com">',
                    'https://www.w3schools.com',
                    ''
                ]
            ]
        ];

        yield [
            'Now the widgets are ready to go. The engine combines the entire tree into a render-able view and tells the operating system to display it. 
             This is called rasterizing, and it’sthe last step.',
            null
        ];
    }
}
