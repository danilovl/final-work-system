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

namespace App\Domain\Media\Bus\Query\MediaFile;

use App\Infrastructure\Interfaces\Bus\QueryInterface;
use App\Domain\Media\Entity\Media;
use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage('sync')]
readonly class GetMediaFileQuery implements QueryInterface
{
    private function __construct(
        public Media $media
    ) {}

    public static function create(Media $media): self
    {
        return new self($media);
    }
}
