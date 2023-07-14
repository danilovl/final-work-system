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

namespace App\Domain\Work\Http;

use App\Application\Constant\{
    FlashTypeConstant};
use App\Application\Form\Factory\FormDeleteFactory;
use App\Application\Service\{
    EntityManagerService,
    RequestService};
use App\Domain\Work\Constant\WorkUserTypeConstant;
use App\Domain\Work\Entity\Work;
use Danilovl\HashidsBundle\Interfaces\HashidsServiceInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\{
    RedirectResponse,
    Request};

readonly class WorkDeleteHandle
{
    public function __construct(
        private RequestService $requestService,
        private HashidsServiceInterface $hashidsService,
        private FormDeleteFactory $formDeleteFactory,
        private EntityManagerService $entityManagerService
    ) {}

    public function handle(Request $request, Work $work): RedirectResponse
    {
        $form = $this->formDeleteFactory
            ->createDeleteForm($work, 'work_delete')
            ->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {

                $workMedia = $work->getMedias();
                if ($workMedia !== null) {
                    foreach ($workMedia as $media) {
                        $deleteFile = $media->getWebPath();
                        (new Filesystem)->remove($deleteFile);
                    }
                }

                $this->entityManagerService->remove($work);

                $this->requestService->addFlashTrans(FlashTypeConstant::SUCCESS->value, 'app.flash.form.delete.success');

                return $this->requestService->redirectToRoute('work_list', [
                    'type' => WorkUserTypeConstant::SUPERVISOR->value
                ]);
            }

            $this->requestService->addFlashTrans(FlashTypeConstant::ERROR->value, 'app.flash.form.delete.error');

            return $this->requestService->redirectToRoute('work_detail', [
                'id' => $this->hashidsService->encode($work->getId())
            ]);
        }

        return $this->requestService->redirectToRoute('work_list', [
            'type' => WorkUserTypeConstant::SUPERVISOR->value
        ]);
    }
}
