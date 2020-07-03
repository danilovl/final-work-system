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

use App\Constant\{
    AjaxJsonTypeConstant,
    VoterSupportConstant
};
use App\Controller\BaseController;
use App\Entity\SystemEventRecipient;
use Symfony\Component\HttpFoundation\JsonResponse;

class SystemEventController extends BaseController
{
    public function viewed(SystemEventRecipient $systemEventRecipient): JsonResponse
    {
        $this->denyAccessUnlessGranted(VoterSupportConstant::CHANGE_VIEWED, $systemEventRecipient);

        $systemEventRecipient->changeViewed();
        $this->flushEntity();

        return $this->createAjaxJson(AjaxJsonTypeConstant::SAVE_SUCCESS);
    }

    public function viewedAll(): JsonResponse
    {
        $user = $this->getUser();
        $isUnreadExist = $this->get('app.facade.system_event')
            ->isUnreadSystemEventsByRecipient($user);

        if ($isUnreadExist) {
            $this->get('app.facade.system_event_recipient')->updateViewedAll($user);
        }

        return $this->createAjaxJson(AjaxJsonTypeConstant::SAVE_SUCCESS);
    }
}
