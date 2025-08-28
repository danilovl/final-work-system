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

namespace App\Domain\Version\Bus\Command\CreateVersion;

use App\Application\Interfaces\Bus\CommandInterface;
use App\Domain\Media\Model\MediaModel;
use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage('sync')]
readonly class CreateVersionCommand implements CommandInterface
{
    private function __construct(public MediaModel $mediaModel) {}

    public static function create(MediaModel $mediaModel): self
    {
        return new self($mediaModel);
    }
}
