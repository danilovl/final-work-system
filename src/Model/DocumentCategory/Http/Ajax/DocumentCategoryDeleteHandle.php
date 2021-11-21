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

namespace App\Model\DocumentCategory\Http\Ajax;

use App\Constant\AjaxJsonTypeConstant;
use App\Model\MediaCategory\Entity\MediaCategory;
use App\Service\{
    RequestService,
    EntityManagerService
};
use Symfony\Component\HttpFoundation\JsonResponse;

class DocumentCategoryDeleteHandle
{
    public function __construct(
        private RequestService $requestService,
        private EntityManagerService $entityManagerService
    ) {
    }

    public function handle(MediaCategory $mediaCategory): JsonResponse
    {
        if (count($mediaCategory->getMedias()) === 0) {
            $this->entityManagerService->remove($mediaCategory);

            return $this->requestService->createAjaxJson(AjaxJsonTypeConstant::DELETE_SUCCESS);
        }

        return $this->requestService->createAjaxJson(AjaxJsonTypeConstant::DELETE_FAILURE);
    }
}
