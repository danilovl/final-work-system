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

use Doctrine\ORM\{
    ORMException,
    OptimisticLockException
};
use FinalWork\FinalWorkBundle\Constant\{
    AjaxJsonTypeConstant,
    VoterSupportConstant
};
use FinalWork\FinalWorkBundle\Controller\BaseController;
use FinalWork\FinalWorkBundle\Entity\ConversationMessage;
use Symfony\Component\HttpFoundation\JsonResponse;

class ConversationController extends BaseController
{
    /**
     * @param ConversationMessage $conversationMessage
     * @return JsonResponse
     *
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function changeReadMessageStatusAction(ConversationMessage $conversationMessage): JsonResponse
    {
        $this->denyAccessUnlessGranted(
            VoterSupportConstant::CHANGE_READ_MESSAGE_STATUS,
            $conversationMessage
        );

        $this->get('final_work.facade.conversation_message')
            ->changeReadMessageStatus($this->getUser(), $conversationMessage);

        return $this->createAjaxJson(AjaxJsonTypeConstant::SAVE_SUCCESS);
    }
}
