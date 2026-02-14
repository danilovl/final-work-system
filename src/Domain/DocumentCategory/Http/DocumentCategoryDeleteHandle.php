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
use App\Application\Interfaces\Bus\CommandBusInterface;
use App\Domain\DocumentCategory\Bus\Command\DeleteDocumentCategory\DeleteDocumentCategoryCommand;
use App\Infrastructure\Service\RequestService;
use App\Domain\MediaCategory\Entity\MediaCategory;
use Symfony\Component\HttpFoundation\RedirectResponse;

readonly class DocumentCategoryDeleteHandle
{
    public function __construct(
        private RequestService $requestService,
        private CommandBusInterface $commandBus
    ) {}

    public function __invoke(MediaCategory $mediaCategory): RedirectResponse
    {
        if (count($mediaCategory->getMedias()) === 0) {
            $command = DeleteDocumentCategoryCommand::create($mediaCategory);
            $this->commandBus->dispatch($command);
        } else {
            $this->requestService->addFlashTrans(FlashTypeConstant::ERROR->value, 'app.flash.form.delete.error');
        }

        return $this->requestService->redirectToRoute('document_category_list');
    }
}
