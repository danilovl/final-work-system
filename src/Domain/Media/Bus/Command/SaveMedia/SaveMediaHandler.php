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

namespace App\Domain\Media\Bus\Command\SaveMedia;

use App\Infrastructure\Interfaces\Bus\CommandHandlerInterface;
use App\Domain\Media\Entity\Media;
use App\Domain\Media\Factory\MediaFactory;

readonly class SaveMediaHandler implements CommandHandlerInterface
{
    public function __construct(private MediaFactory $mediaFactory) {}

    public function __invoke(SaveMediaCommand $command): Media
    {
        return $this->mediaFactory->flushFromModel(
            mediaModel: $command->mediaModel,
            media: $command->media
        );
    }
}
