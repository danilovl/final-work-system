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

namespace App\Tests\Unit\Application\Service;

use App\Application\Service\TranslatorService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\{
    Request,
    RequestStack
};
use Symfony\Contracts\Translation\TranslatorInterface;

class TranslatorServiceTest extends TestCase
{
    private TranslatorService $translatorService;

    private MockObject $translatorMock;

    private MockObject $requestStackMock;

    protected function setUp(): void
    {
        $this->translatorMock = $this->createMock(TranslatorInterface::class);
        $this->requestStackMock = $this->createMock(RequestStack::class);

        $this->translatorService = new TranslatorService(
            $this->translatorMock,
            $this->requestStackMock,
            'en'
        );
    }

    public function testTrans(): void
    {
        $id = 'translation.key';
        $parameters = ['param1' => 'value1', 'param2' => 'value2'];
        $domain = 'messages';
        $locale = 'ru';

        $requestMock = $this->createMock(Request::class);
        $requestMock->expects($this->any())
            ->method('getLocale')
            ->willReturn($locale);

        $this->requestStackMock
            ->expects($this->any())
            ->method('getCurrentRequest')
            ->willReturn($requestMock);

        $this->translatorMock
            ->expects($this->any())
            ->method('trans')
            ->with($id, $parameters, $domain, $locale)
            ->willReturn('Translated string');

        $result = $this->translatorService->trans($id, $parameters, $domain, $locale);

        $this->assertEquals('Translated string', $result);
    }

    public function testGetLocale(): void
    {
        $result = $this->translatorService->getLocale();

        $this->assertEquals('en', $result);
    }
}
