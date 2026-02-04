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

namespace App\Infrastructure\EventSubscriber;

use App\Application\Constant\FlashTypeConstant;
use App\Application\EventSubscriber\Events;
use App\Infrastructure\Service\RequestService;
use Override;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

readonly class RequestFlashSubscriber implements EventSubscriberInterface
{
    public function __construct(private RequestService $requestService) {}

    #[Override]
    public static function getSubscribedEvents(): array
    {
        return [
            Events::ENTITY_CREATE => 'onCreateSuccess',
            Events::ENTITY_REMOVE => 'onDeleteSuccess',
            Events::ENTITY_SAVE => 'onSaveSuccess',
            Events::REQUEST_FLASH_CREATE_FAILURE => 'onCreateFailure',
            Events::REQUEST_FLASH_SAVE_FAILURE => 'onSaveFailure',
            Events::REQUEST_FLASH_DELETE_FAILURE => 'onDeleteFailure'
        ];
    }

    public function onCreateSuccess(): void
    {
        $this->requestService->addFlashTransAutoType(FlashTypeConstant::CREATE_SUCCESS);
    }

    public function onDeleteSuccess(): void
    {
        $this->requestService->addFlashTransAutoType(FlashTypeConstant::DELETE_SUCCESS);
    }

    public function onSaveSuccess(): void
    {
        $this->requestService->addFlashTransAutoType(FlashTypeConstant::SAVE_SUCCESS);
    }

    public function onCreateFailure(): void
    {
        $this->requestService->addFlashTransAutoType(FlashTypeConstant::CREATE_WARNING);
        $this->requestService->addFlashTransAutoType(FlashTypeConstant::CREATE_ERROR);
    }

    public function onSaveFailure(): void
    {
        $this->requestService->addFlashTransAutoType(FlashTypeConstant::SAVE_WARNING);
        $this->requestService->addFlashTransAutoType(FlashTypeConstant::SAVE_ERROR);
    }

    public function onDeleteFailure(): void
    {
        $this->requestService->addFlashTransAutoType(FlashTypeConstant::DELETE_WARNING);
        $this->requestService->addFlashTransAutoType(FlashTypeConstant::DELETE_ERROR);
    }
}


