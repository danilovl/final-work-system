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

namespace App\Tests\Unit\Infrastructure\Event\EventSubscriber;

use App\Application\Constant\FlashTypeConstant;
use App\Application\EventSubscriber\Events;
use App\Infrastructure\Event\EventSubscriber\RequestFlashSubscriber;
use App\Infrastructure\Service\RequestService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class RequestFlashSubscriberTest extends TestCase
{
    private MockObject&RequestService $requestService;

    private RequestFlashSubscriber $subscriber;

    protected function setUp(): void
    {
        $this->requestService = $this->createMock(RequestService::class);
        $this->subscriber = new RequestFlashSubscriber($this->requestService);
    }

    public function testGetSubscribedEvents(): void
    {
        $subscribedEvents = $this->subscriber::getSubscribedEvents();

        $this->assertEquals('onCreateSuccess', $subscribedEvents[Events::ENTITY_CREATE]);
        $this->assertEquals('onDeleteSuccess', $subscribedEvents[Events::ENTITY_REMOVE]);
        $this->assertEquals('onSaveSuccess', $subscribedEvents[Events::ENTITY_SAVE]);
        $this->assertEquals('onCreateFailure', $subscribedEvents[Events::REQUEST_FLASH_CREATE_FAILURE]);
        $this->assertEquals('onSaveFailure', $subscribedEvents[Events::REQUEST_FLASH_SAVE_FAILURE]);
        $this->assertEquals('onDeleteFailure', $subscribedEvents[Events::REQUEST_FLASH_DELETE_FAILURE]);
    }

    public function testOnCreateSuccess(): void
    {
        $this->requestService
            ->expects($this->once())
            ->method('addFlashTransAutoType')
            ->with(FlashTypeConstant::CREATE_SUCCESS);

        $this->subscriber->onCreateSuccess();
    }

    public function testOnDeleteSuccess(): void
    {
        $this->requestService
            ->expects($this->once())
            ->method('addFlashTransAutoType')
            ->with(FlashTypeConstant::DELETE_SUCCESS);

        $this->subscriber->onDeleteSuccess();
    }

    public function testOnSaveSuccess(): void
    {
        $this->requestService
            ->expects($this->once())
            ->method('addFlashTransAutoType')
            ->with(FlashTypeConstant::SAVE_SUCCESS);

        $this->subscriber->onSaveSuccess();
    }

    public function testOnCreateFailure(): void
    {
        $this->requestService
            ->expects($this->exactly(2))
            ->method('addFlashTransAutoType')
            ->willReturnCallback(function (FlashTypeConstant $type): void {
                static $callCount = 0;

                if ($callCount === 0) {
                    $this->assertEquals(FlashTypeConstant::CREATE_WARNING, $type);
                } else if ($callCount === 1) {
                    $this->assertEquals(FlashTypeConstant::CREATE_ERROR, $type);
                }

                $callCount++;
            });

        $this->subscriber->onCreateFailure();
    }

    public function testOnSaveFailure(): void
    {
        $this->requestService
            ->expects($this->exactly(2))
            ->method('addFlashTransAutoType')
            ->willReturnCallback(function (FlashTypeConstant $type): void {
                static $callCount = 0;

                if ($callCount === 0) {
                    $this->assertEquals(FlashTypeConstant::SAVE_WARNING, $type);
                } else if ($callCount === 1) {
                    $this->assertEquals(FlashTypeConstant::SAVE_ERROR, $type);
                }

                $callCount++;
            });

        $this->subscriber->onSaveFailure();
    }

    public function testOnDeleteFailure(): void
    {
        $this->requestService
            ->expects($this->exactly(2))
            ->method('addFlashTransAutoType')
            ->willReturnCallback(function (FlashTypeConstant $type): void {
                static $callCount = 0;

                if ($callCount === 0) {
                    $this->assertEquals(FlashTypeConstant::DELETE_WARNING, $type);
                } else if ($callCount === 1) {
                    $this->assertEquals(FlashTypeConstant::DELETE_ERROR, $type);
                }

                $callCount++;
            });

        $this->subscriber->onDeleteFailure();
    }
}
