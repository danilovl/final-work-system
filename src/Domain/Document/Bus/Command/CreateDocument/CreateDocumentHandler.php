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

namespace App\Domain\Document\Bus\Command\CreateDocument;

use App\Application\Interfaces\Bus\CommandHandlerInterface;
use App\Domain\Document\EventDispatcher\DocumentEventDispatcher;
use App\Domain\Media\Factory\MediaFactory;

readonly class CreateDocumentHandler implements CommandHandlerInterface
{
    public function __construct(
        private MediaFactory $mediaFactory,
        private DocumentEventDispatcher $documentEventDispatcher
    ) {}

    public function __invoke(CreateDocumentCommand $command): void
    {
        $media = $this->mediaFactory->flushFromModel($command->mediaModel);
        $this->documentEventDispatcher->onDocumentCreate($media);
    }
}
