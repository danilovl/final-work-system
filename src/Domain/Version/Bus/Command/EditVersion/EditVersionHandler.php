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

namespace App\Domain\Version\Bus\Command\EditVersion;

use App\Application\Interfaces\Bus\CommandHandlerInterface;
use App\Domain\Media\Entity\Media;
use App\Domain\Media\Factory\MediaFactory;

readonly class EditVersionHandler implements CommandHandlerInterface
{
    public function __construct(private MediaFactory $mediaFactory) {}

    public function __invoke(EditVersionCommand $command): Media
    {
        return $this->mediaFactory->flushFromModel($command->mediaModel, $command->media);
    }
}
