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

namespace App\Tests\Unit\Domain\ResetPassword\EventDispatcher;

use App\Application\EventSubscriber\Events;
use App\Domain\ResetPassword\Entity\ResetPassword;
use App\Domain\ResetPassword\EventDispatcher\GenericEvent\ResetPasswordGenericEvent;
use App\Domain\ResetPassword\EventDispatcher\SecurityDispatcherService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class SecurityDispatcherServiceTest extends TestCase
{
    public function testOnResetPasswordTokenCreate(): void
    {
        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $securityDispatcherService = new SecurityDispatcherService($eventDispatcher);

        $resetPassword = $this->createMock(ResetPassword::class);

        $eventDispatcher->expects($this->once())
            ->method('dispatch')
            ->with(
                $this->isInstanceOf(ResetPasswordGenericEvent::class),
                Events::SECURITY_RESET_PASSWORD_TOKEN
            );

        $securityDispatcherService->onResetPasswordTokenCreate($resetPassword, 3_600);
    }
}
