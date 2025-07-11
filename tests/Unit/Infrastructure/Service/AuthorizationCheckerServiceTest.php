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

use App\Infrastructure\Service\AuthorizationCheckerService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use stdClass;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class AuthorizationCheckerServiceTest extends TestCase
{
    private AuthorizationCheckerService $authorizationCheckerService;

    private MockObject&AuthorizationCheckerInterface $authorizationChecker;

    protected function setUp(): void
    {
        $this->authorizationChecker = $this->createMock(AuthorizationCheckerInterface::class);

        $this->authorizationCheckerService = new AuthorizationCheckerService($this->authorizationChecker);
    }

    public function testDenyAccessUnlessGrantedTrue(): void
    {
        $this->authorizationChecker
            ->expects($this->once())
            ->method('isGranted')
            ->willReturn(true);

        $this->authorizationCheckerService->denyAccessUnlessGranted('test', new stdClass);
    }

    public function testDenyAccessUnlessGrantedFalse(): void
    {
        $this->expectException(AccessDeniedException::class);

        $this->authorizationChecker
            ->expects($this->once())
            ->method('isGranted')
            ->willReturn(false);

        $this->authorizationCheckerService->denyAccessUnlessGranted('test', new stdClass);
    }
}
