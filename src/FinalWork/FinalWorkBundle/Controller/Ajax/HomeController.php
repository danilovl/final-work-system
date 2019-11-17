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

namespace FinalWork\FinalWorkBundle\Controller\Ajax;

use FinalWork\FinalWorkBundle\Constant\{
    AjaxJsonTypeConstant,
    VoterSupportConstant
};
use Doctrine\ORM\{
    ORMException,
    OptimisticLockException
};
use FinalWork\FinalWorkBundle\Controller\BaseController;
use FinalWork\FinalWorkBundle\Entity\SystemEventRecipient;
use Symfony\Component\HttpFoundation\JsonResponse;

class HomeController extends BaseController
{
    /**
     * @param SystemEventRecipient $systemEventRecipient
     * @return JsonResponse
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function viewedAction(SystemEventRecipient $systemEventRecipient): JsonResponse
    {
        $this->denyAccessUnlessGranted(VoterSupportConstant::CHANGE_VIEWED, $systemEventRecipient);

        $systemEventRecipient->changeViewed();
        $this->flushEntity();

        return $this->createAjaxJson(AjaxJsonTypeConstant::SAVE_SUCCESS);
    }
}
