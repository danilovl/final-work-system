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

namespace App\Domain\Document\Model;

use App\Domain\MediaCategory\Entity\MediaCategory;
use App\Domain\MediaMimeType\Entity\MediaMimeType;

class DocumentSearchModel
{
    public ?string $name = null;

    /**
     * @var MediaCategory[]
     */
    public array $categories = [];

    /**
     * @var MediaMimeType[]
     */
    public array $mimeType = [];

    /**
     * Criteria for MediaRepository, keys are the searched fields.
     *
     * @return array{name: ?string, categories: MediaCategory[], mimeType: MediaMimeType[]}
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'categories' => $this->categories,
            'mimeType' => $this->mimeType
        ];
    }
}
