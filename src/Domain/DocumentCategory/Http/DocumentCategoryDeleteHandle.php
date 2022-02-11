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

namespace App\Domain\DocumentCategory\Http;

use App\Application\Constant\FlashTypeConstant;
use App\Application\Service\{
    RequestService,
    EntityManagerService
};
use App\Domain\MediaCategory\Entity\MediaCategory;
use Symfony\Component\HttpFoundation\RedirectResponse;

class DocumentCategoryDeleteHandle
{
    public function __construct(
        private RequestService $requestService,
        private EntityManagerService $entityManagerService
    ) {
    }

    public function handle(MediaCategory $mediaCategory): RedirectResponse
    {
        if (count($mediaCategory->getMedias()) === 0) {
            $this->entityManagerService->remove($mediaCategory);

            $this->requestService->addFlashTrans(FlashTypeConstant::SUCCESS, 'app.flash.form.delete.success');
        } else {
            $this->requestService->addFlashTrans(FlashTypeConstant::ERROR, 'app.flash.form.delete.error');
        }

        return $this->requestService->redirectToRoute('document_category_list');
    }
}
