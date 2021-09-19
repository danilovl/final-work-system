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

namespace App\Controller\Ajax;

use App\Constant\VoterSupportConstant;
use App\Controller\BaseController;
use App\Entity\SystemEventRecipient;
use Symfony\Component\HttpFoundation\JsonResponse;

class SystemEventController extends BaseController
{
    public function viewed(SystemEventRecipient $systemEventRecipient): JsonResponse
    {
        $this->denyAccessUnlessGranted(VoterSupportConstant::CHANGE_VIEWED, $systemEventRecipient);

        return $this->get('app.http_handle_ajax.system_event.viewed')->handle($systemEventRecipient);
    }

    public function viewedAll(): JsonResponse
    {
        return $this->get('app.http_handle_ajax.system_event.viewed_all')->handle();
    }
}
