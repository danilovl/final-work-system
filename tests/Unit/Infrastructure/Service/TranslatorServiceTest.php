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

namespace App\Tests\Unit\Infrastructure\Service;

use App\Infrastructure\Service\TranslatorService;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\{
    Request,
    RequestStack
};
use Symfony\Contracts\Translation\TranslatorInterface;

class TranslatorServiceTest extends TestCase
{
    private TranslatorService $translatorService;

    private Stub&TranslatorInterface $translator;

    private Stub&RequestStack $requestStack;

    protected function setUp(): void
    {
        $this->translator = $this->createStub(TranslatorInterface::class);
        $this->requestStack = $this->createStub(RequestStack::class);

        $this->translatorService = new TranslatorService(
            translator: $this->translator,
            requestStack: $this->requestStack,
            locale: 'en'
        );
    }

    public function testTrans(): void
    {
        $id = 'translation.key';
        $parameters = ['param1' => 'value1', 'param2' => 'value2'];
        $domain = 'messages';
        $locale = 'ru';

        /** @var Stub&Request $request */
        $request = $this->createStub(Request::class);
        $request->method('getLocale')
            ->willReturn($locale);

        $this->requestStack
            ->method('getCurrentRequest')
            ->willReturn($request);

        $this->translator
            ->method('trans')
            ->with($id, $parameters, $domain, $locale)
            ->willReturn('Translated string');

        $result = $this->translatorService->trans(
            id: $id,
            parameters: $parameters,
            domain: $domain,
            locale: $locale
        );

        $this->assertEquals('Translated string', $result);
    }

    public function testGetLocale(): void
    {
        $result = $this->translatorService->getLocale();

        $this->assertEquals('en', $result);
    }
}
