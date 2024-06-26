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

namespace App\Domain\DocumentCategory\Http\Ajax;

use App\Application\Constant\AjaxJsonTypeConstant;
use App\Application\Service\{
    RequestService,
    EntityManagerService
};
use App\Domain\MediaCategory\Entity\MediaCategory;
use Symfony\Component\HttpFoundation\JsonResponse;

readonly class DocumentCategoryDeleteHandle
{
    public function __construct(
        private RequestService $requestService,
        private EntityManagerService $entityManagerService
    ) {}

    public function __invoke(MediaCategory $mediaCategory): JsonResponse
    {
        if (count($mediaCategory->getMedias()) === 0) {
            $this->entityManagerService->remove($mediaCategory);

            return $this->requestService->createAjaxJson(AjaxJsonTypeConstant::DELETE_SUCCESS);
        }

        return $this->requestService->createAjaxJson(AjaxJsonTypeConstant::DELETE_FAILURE);
    }
}
