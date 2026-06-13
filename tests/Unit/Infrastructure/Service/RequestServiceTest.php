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

use App\Application\Constant\{
    AjaxJsonTypeConstant,
    FlashTypeConstant
};
use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\{
    Request,
    RequestStack,
    Response
};
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\RouterInterface;

class RequestServiceTest extends TestCase
{
    private RequestStack $requestStack;

    private \App\Infrastructure\Service\RequestService $requestService;

    protected function setUp(): void
    {
        $request = new Request;
        $request->setSession(new Session);

        $this->requestStack = new RequestStack;
        $this->requestStack->push($request);

        $router = $this->createMock(RouterInterface::class);
        $router->expects($this->any())
            ->method('generate')
            ->willReturn('url');

        $translator = $this->createMock(\App\Infrastructure\Service\TranslatorService::class);
        $translator->expects($this->any())
            ->method('trans')
            ->willReturn('trans');

        $this->requestService = new \App\Infrastructure\Service\RequestService($this->requestStack, $router, $translator);
    }

    public function testAddFlash(): void
    {
        $this->requestService->addFlash(FlashTypeConstant::ERROR->value, 'error');

        /** @var Session $session */
        $session = $this->requestStack->getSession();
        $message = $session->getFlashBag()->get(FlashTypeConstant::ERROR->value);

        $this->assertEquals('error', $message[0]);
    }

    public function testAddFlashTrans(): void
    {
        $this->requestService->addFlashTrans(FlashTypeConstant::ERROR->value, 'error');

        /** @var Session $session */
        $session = $this->requestStack->getSession();
        $message = $session->getFlashBag()->get(FlashTypeConstant::ERROR->value);

        $this->assertEquals('trans', $message[0]);
    }

    public function testRedirectToRoute(): void
    {
        $redirect = $this->requestService->redirectToRoute('url');

        $this->assertSame('url', $redirect->getTargetUrl());
        $this->assertSame(Response::HTTP_FOUND, $redirect->getStatusCode());
    }

    public function testGetSession(): void
    {
        $this->expectNotToPerformAssertions();

        $this->requestService->getSession();
    }

    #[DataProvider('provideCreateAjaxJsonCases')]
    public function testCreateAjaxJson(AjaxJsonTypeConstant $type, array $expectedResult): void
    {
        $expectedResult = array_merge($expectedResult, ['test' => 'test']);
        $result = $this->requestService->createAjaxJson($type, ['test' => 'test']);
        /** @var string $content */
        $content = $result->getContent();

        $this->assertSame($expectedResult, json_decode($content, true));
    }

    public static function provideCreateAjaxJsonCases(): Generator
    {
        yield [
            AjaxJsonTypeConstant::CREATE_SUCCESS,
            [
                'valid' => true,
                'notifyMessage' => [
                    FlashTypeConstant::SUCCESS->value => 'trans'
                ]
            ]
        ];

        yield [
            AjaxJsonTypeConstant::CREATE_FAILURE,
            [
                'valid' => false,
                'notifyMessage' => [
                    FlashTypeConstant::ERROR->value => 'trans',
                    FlashTypeConstant::WARNING->value => 'trans'
                ]
            ]
        ];

        yield [
            AjaxJsonTypeConstant::SAVE_SUCCESS,
            [
                'valid' => true,
                'notifyMessage' => [
                    FlashTypeConstant::SUCCESS->value => 'trans'
                ]
            ]
        ];

        yield [
            AjaxJsonTypeConstant::SAVE_FAILURE,
            [
                'valid' => false,
                'notifyMessage' => [
                    FlashTypeConstant::ERROR->value => 'trans',
                    FlashTypeConstant::WARNING->value => 'trans'
                ]
            ]
        ];

        yield [
            AjaxJsonTypeConstant::DELETE_SUCCESS,
            [
                'delete' => true,
                'notifyMessage' => [
                    FlashTypeConstant::SUCCESS->value => 'trans'
                ]
            ]
        ];

        yield [
            AjaxJsonTypeConstant::DELETE_FAILURE,
            [
                'delete' => false,
                'notifyMessage' => [
                    FlashTypeConstant::ERROR->value => 'trans',
                    FlashTypeConstant::WARNING->value => 'trans'
                ]
            ]
        ];
    }
}
