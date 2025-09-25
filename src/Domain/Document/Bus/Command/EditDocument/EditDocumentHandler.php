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

namespace App\Domain\Document\Bus\Command\EditDocument;

use App\Application\Interfaces\Bus\CommandHandlerInterface;
use App\Domain\Media\Factory\MediaFactory;

readonly class EditDocumentHandler implements CommandHandlerInterface
{
    public function __construct(private MediaFactory $mediaFactory) {}

    public function __invoke(EditDocumentCommand $command): void
    {
        $this->mediaFactory->flushFromModel($command->mediaModel, $command->media);
    }
}
