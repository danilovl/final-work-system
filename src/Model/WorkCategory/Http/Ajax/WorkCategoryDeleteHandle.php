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

namespace App\Model\WorkCategory\Http\Ajax;

use App\Constant\AjaxJsonTypeConstant;
use App\Entity\WorkCategory;
use App\Service\{
    RequestService,
    EntityManagerService
};
use Symfony\Component\HttpFoundation\Response;

class WorkCategoryDeleteHandle
{
    public function __construct(
        private RequestService $requestService,
        private EntityManagerService $entityManagerService
    ) {
    }

    public function handle(WorkCategory $workCategory): Response
    {
        if (count($workCategory->getWorks()) > 0) {
            return $this->requestService->createAjaxJson(AjaxJsonTypeConstant::DELETE_FAILURE);
        }

        $this->entityManagerService->remove($workCategory);

        return $this->requestService->createAjaxJson(AjaxJsonTypeConstant::DELETE_SUCCESS);
    }
}
