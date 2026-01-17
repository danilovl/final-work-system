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

namespace App\Domain\SystemEvent\Controller\Ajax;

use App\Application\Constant\VoterSupportConstant;
use App\Infrastructure\Service\AuthorizationCheckerService;
use App\Domain\SystemEvent\Http\Ajax\{
    SystemEventViewedHandle,
    SystemEventViewedAllHandle
};
use App\Domain\SystemEventRecipient\Entity\SystemEventRecipient;
use Symfony\Component\HttpFoundation\JsonResponse;

readonly class SystemEventController
{
    public function __construct(
        private AuthorizationCheckerService $authorizationCheckerService,
        private SystemEventViewedHandle $systemEventViewedHandle,
        private SystemEventViewedAllHandle $systemEventViewedAllHandle
    ) {}

    public function viewed(SystemEventRecipient $systemEventRecipient): JsonResponse
    {
        $this->authorizationCheckerService->denyAccessUnlessGranted(VoterSupportConstant::CHANGE_VIEWED->value, $systemEventRecipient);

        return $this->systemEventViewedHandle->__invoke($systemEventRecipient);
    }

    public function viewedAll(): JsonResponse
    {
        return $this->systemEventViewedAllHandle->__invoke();
    }
}
