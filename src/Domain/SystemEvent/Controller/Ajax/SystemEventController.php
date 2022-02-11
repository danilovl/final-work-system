<?php declare(strict_types=1);

/*
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
use App\Domain\SystemEvent\Http\Ajax\{
    SystemEventViewedHandle,
    SystemEventViewedAllHandle
};
use App\Domain\SystemEventRecipient\Entity\SystemEventRecipient;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;

class SystemEventController extends AbstractController
{
    public function __construct(
        private SystemEventViewedHandle $systemEventViewedHandle,
        private SystemEventViewedAllHandle $systemEventViewedAllHandle
    ) {
    }

    public function viewed(SystemEventRecipient $systemEventRecipient): JsonResponse
    {
        $this->denyAccessUnlessGranted(VoterSupportConstant::CHANGE_VIEWED, $systemEventRecipient);

        return $this->systemEventViewedHandle->handle($systemEventRecipient);
    }

    public function viewedAll(): JsonResponse
    {
        return $this->systemEventViewedAllHandle->handle();
    }
}
