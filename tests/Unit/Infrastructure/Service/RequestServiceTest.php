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
use App\Application\Exception\ConstantNotFoundException;
use App\Infrastructure\Service\{
    RequestService,
    TranslatorService
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

    private RequestService $requestService;

    protected function setUp(): void
    {
        $session = new Session;
        $session->clear();
        $session->getFlashBag()->clear();

        $request = new Request;
        $request->setSession($session);

        $this->requestStack = new RequestStack;
        $this->requestStack->push($request);

        $router = $this->createStub(RouterInterface::class);
        $router->method('generate')
            ->willReturn('url');

        $translator = $this->createStub(TranslatorService::class);
        $translator->method('trans')
            ->willReturn('trans');

        $this->requestService = new RequestService($this->requestStack, $router, $translator);
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
    public function testCreateAjaxJson(AjaxJsonTypeConstant $type, array $expectedResult, int $expectedStatus): void
    {
        $expectedResult = array_merge($expectedResult, ['test' => 'test']);
        $result = $this->requestService->createAjaxJson($type, ['test' => 'test']);
        /** @var string $content */
        $content = $result->getContent();

        $this->assertSame($expectedResult, json_decode($content, true));
        $this->assertSame($expectedStatus, $result->getStatusCode());
    }

    public function testStatusCodeOverride(): void
    {
        $result = $this->requestService->createAjaxJson(
            type: AjaxJsonTypeConstant::CREATE_FAILURE,
            statusCode: Response::HTTP_I_AM_A_TEAPOT
        );

        $this->assertSame(Response::HTTP_I_AM_A_TEAPOT, $result->getStatusCode());
    }

    public function testExtraDataMergingAndOverride(): void
    {
        $extra = [
            'valid' => false,
            'extraKey' => 'extraValue'
        ];
        $response = $this->requestService->createAjaxJson(AjaxJsonTypeConstant::CREATE_SUCCESS, $extra);
        /** @var array $data */
        $data = json_decode((string) $response->getContent(), true);

        $this->assertFalse($data['valid']);
        $this->assertSame('extraValue', $data['extraKey']);
        $this->assertArrayHasKey('notifyMessage', $data);
    }

    public function testWithoutExtraData(): void
    {
        $response = $this->requestService->createAjaxJson(AjaxJsonTypeConstant::SAVE_SUCCESS);
        /** @var array $dataNull */
        $dataNull = json_decode((string) $response->getContent(), true);

        $expected = [
            'valid' => true,
            'notifyMessage' => [
                FlashTypeConstant::SUCCESS->value => 'trans'
            ]
        ];
        $this->assertSame($expected, $dataNull);

        $responseEmpty = $this->requestService->createAjaxJson(AjaxJsonTypeConstant::SAVE_SUCCESS, []);
        $dataEmpty = json_decode((string) $responseEmpty->getContent(), true);

        $this->assertSame($expected, $dataEmpty);
    }

    public function testFlashWithMainType(): void
    {
        $this->requestService->addFlashTransAutoType(FlashTypeConstant::SAVE_ERROR);

        /** @var Session $session */
        $session = $this->requestStack->getSession();
        $messages = $session->getFlashBag()->get(FlashTypeConstant::ERROR->value);

        $this->assertCount(1, $messages);
        $this->assertSame('trans', $messages[0]);
    }

    public function testIdempotentForSameType(): void
    {
        $this->requestService->addFlashTransAutoType(FlashTypeConstant::CREATE_SUCCESS);
        $this->requestService->addFlashTransAutoType(FlashTypeConstant::CREATE_SUCCESS);

        /** @var Session $session */
        $session = $this->requestStack->getSession();
        $messages = $session->getFlashBag()->get(FlashTypeConstant::SUCCESS->value);

        $this->assertCount(1, $messages);
        $this->assertSame('trans', $messages[0]);
    }

    public function testAddedSeparately(): void
    {
        $this->requestService->addFlashTransAutoType(FlashTypeConstant::CREATE_SUCCESS);
        $this->requestService->addFlashTransAutoType(FlashTypeConstant::DELETE_WARNING);
        $this->requestService->addFlashTransAutoType(FlashTypeConstant::DELETE_ERROR);

        /** @var Session $session */
        $session = $this->requestStack->getSession();

        $success = $session->getFlashBag()->get(FlashTypeConstant::SUCCESS->value);
        $warning = $session->getFlashBag()->get(FlashTypeConstant::DELETE_WARNING->value);
        $error = $session->getFlashBag()->get(FlashTypeConstant::ERROR->value);

        $this->assertCount(1, $success);
        $this->assertSame('trans', $success[0]);

        $this->assertCount(1, $warning);
        $this->assertSame('trans', $warning[0]);

        $this->assertCount(1, $error);
        $this->assertSame('trans', $error[0]);
    }

    public function testNotFound(): void
    {
        $this->expectException(ConstantNotFoundException::class);

        $this->requestService->addFlashTransAutoType(FlashTypeConstant::SUCCESS);
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
            ],
            Response::HTTP_CREATED
        ];

        yield [
            AjaxJsonTypeConstant::CREATE_FAILURE,
            [
                'valid' => false,
                'notifyMessage' => [
                    FlashTypeConstant::ERROR->value => 'trans',
                    FlashTypeConstant::WARNING->value => 'trans'
                ]
            ],
            Response::HTTP_BAD_REQUEST
        ];

        yield [
            AjaxJsonTypeConstant::SAVE_SUCCESS,
            [
                'valid' => true,
                'notifyMessage' => [
                    FlashTypeConstant::SUCCESS->value => 'trans'
                ]
            ],
            Response::HTTP_OK
        ];

        yield [
            AjaxJsonTypeConstant::SAVE_FAILURE,
            [
                'valid' => false,
                'notifyMessage' => [
                    FlashTypeConstant::ERROR->value => 'trans',
                    FlashTypeConstant::WARNING->value => 'trans'
                ]
            ],
            Response::HTTP_BAD_REQUEST
        ];

        yield [
            AjaxJsonTypeConstant::DELETE_SUCCESS,
            [
                'delete' => true,
                'notifyMessage' => [
                    FlashTypeConstant::SUCCESS->value => 'trans'
                ]
            ],
            Response::HTTP_OK
        ];

        yield [
            AjaxJsonTypeConstant::DELETE_FAILURE,
            [
                'delete' => false,
                'notifyMessage' => [
                    FlashTypeConstant::ERROR->value => 'trans',
                    FlashTypeConstant::WARNING->value => 'trans'
                ]
            ],
            Response::HTTP_BAD_REQUEST
        ];
    }
}
