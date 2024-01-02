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

namespace App\Tests\Unit\Application\EventListener;

use App\Application\EventListener\LoggableListener;
use Danilovl\ParameterBundle\Interfaces\ParameterServiceInterface;
use Gedmo\Loggable\Entity\MappedSuperclass\AbstractLogEntry;
use Gedmo\Loggable\Mapping\Event\LoggableAdapter;
use Danilovl\AsyncBundle\Service\AsyncService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\MessageBusInterface;

class LoggableListenerTest extends TestCase
{
    private readonly MessageBusInterface $messageBus;
    private readonly ParameterServiceInterface $parameterService;
    private readonly AsyncService $asyncService;
    private readonly LoggableListener $listener;

    protected function setUp(): void
    {
        $this->messageBus = $this->createMock(MessageBusInterface::class);
        $this->messageBus
            ->expects($this->any())
            ->method('dispatch');

        $this->parameterService = $this->createMock(ParameterServiceInterface::class);
        $this->asyncService = new AsyncService;

        $this->listener = new class ($this->messageBus, $this->parameterService, $this->asyncService) extends LoggableListener {
            public function createLogEntryPublic($action, $object, LoggableAdapter $ea): ?AbstractLogEntry
            {
                return $this->createLogEntry($action, $object, $ea);
            }
        };
    }

    public function testCreateLogEntryNotEnable(): void
    {
        $this->parameterService
            ->expects($this->once())
            ->method('getBoolean')
            ->willReturn(false);

        $loggableAdapter = $this->createMock(LoggableAdapter::class);

        $this->listener->createLogEntryPublic('action', 'object', $loggableAdapter);
        $this->asyncService->call();
    }
}
