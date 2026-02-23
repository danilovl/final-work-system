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

namespace App\Domain\WorkCategory\Http;

use App\Application\Constant\FlashTypeConstant;
use App\Infrastructure\Service\{
    RequestService,
    EntityManagerService
};
use App\Domain\WorkCategory\Entity\WorkCategory;
use Symfony\Component\HttpFoundation\RedirectResponse;

readonly class WorkCategoryDeleteHandle
{
    public function __construct(
        private RequestService $requestService,
        private EntityManagerService $entityManagerService
    ) {}

    public function __invoke(WorkCategory $workCategory): RedirectResponse
    {
        if (count($workCategory->getWorks()) === 0) {
            $this->entityManagerService->remove($workCategory);
        } else {
            $this->requestService->addFlashTrans(FlashTypeConstant::ERROR->value, 'app.flash.form.delete.error');
        }

        return $this->requestService->redirectToRoute('work_category_list');
    }
}
