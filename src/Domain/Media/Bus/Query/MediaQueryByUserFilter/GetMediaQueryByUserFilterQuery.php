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

namespace App\Domain\Media\Bus\Query\MediaQueryByUserFilter;

use App\Application\Interfaces\Bus\QueryInterface;
use App\Domain\Media\DTO\Repository\MediaRepositoryDTO;
use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage('sync')]
readonly class GetMediaQueryByUserFilterQuery implements QueryInterface
{
    private function __construct(
        public MediaRepositoryDTO $mediaRepositoryDTO
    ) {}

    public static function create(MediaRepositoryDTO $mediaRepositoryDTO): self
    {
        return new self($mediaRepositoryDTO);
    }
}
