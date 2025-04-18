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

namespace App\Domain\DocumentCategory\Bus\Command\CreateDocumentCategory;

use App\Application\Interfaces\Bus\CommandInterface;
use App\Domain\MediaCategory\Model\MediaCategoryModel;
use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage('sync')]
readonly class CreateDocumentCategoryCommand implements CommandInterface
{
    private function __construct(public MediaCategoryModel $mediaCategoryModel) {}

    public static function create(MediaCategoryModel $mediaCategoryModel): self
    {
        return new self($mediaCategoryModel);
    }
}
