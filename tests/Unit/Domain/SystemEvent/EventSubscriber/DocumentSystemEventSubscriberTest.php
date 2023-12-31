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

namespace App\Tests\Unit\Domain\SystemEvent\EventSubscriber;

use App\Domain\SystemEvent\EventSubscriber\DocumentSystemEventSubscriber;
use App\Domain\User\Service\UserWorkService;

class DocumentSystemEventSubscriberTest extends BaseSystemEventSubscriber
{
    protected static string $classSubscriber = DocumentSystemEventSubscriber::class;
    protected readonly DocumentSystemEventSubscriber $subscriber;

    protected function setUp(): void
    {
        parent::setUp();

        $userWorkService = $this->createMock(UserWorkService::class);

        $this->subscriber = new DocumentSystemEventSubscriber(
            $this->entityManagerService,
            $userWorkService,
        );
    }
}
