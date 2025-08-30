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

use App\Application\Interfaces\Bus\CommandInterface;
use App\Domain\Media\Entity\Media;
use App\Domain\Media\Model\MediaModel;
use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage('sync')]
readonly class EditVersionCommand implements CommandInterface
{
    private function __construct(public Media $media, public MediaModel $mediaModel) {}

    public static function create(Media $media, MediaModel $mediaModel): self
    {
        return new self($media, $mediaModel);
    }
}
