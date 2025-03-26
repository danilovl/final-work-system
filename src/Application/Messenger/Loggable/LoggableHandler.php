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

namespace App\Application\Messenger\Loggable;

use App\Application\Service\EntityManagerService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class LoggableHandler
{
    public function __construct(
        private EntityManagerService $entityManagerService,
        private bool $printMessage = true
    ) {}

    public function __invoke(LoggableMessage $loggableMessage): void
    {
        $logEntry = $loggableMessage->logEntry;
        $logEntry->setLoggedAt();

        $this->entityManagerService->persistAndFlush($logEntry);

        if ($this->printMessage) {
            echo sprintf('Success create log for class "%s" and username "%s". %s', $logEntry->getObjectClass(), $logEntry->getUsername(), PHP_EOL);
        }
    }
}
