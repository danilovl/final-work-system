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

namespace App\Domain\WorkSearch;

class WorkSearchModel
{
    public ?string $title = null;
    public ?string $shortcut = null;
    public ?iterable $status = null;
    public ?iterable $type = null;
    public ?iterable $author = null;
    public ?iterable $supervisor = null;
    public ?iterable $opponent = null;
    public ?iterable $consultant = null;
    public ?iterable $deadline = null;
}
