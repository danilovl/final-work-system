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

namespace App\Domain\UserGroup\Bus\Query\UserGroupList;

use App\Application\Interfaces\Bus\QueryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage('sync')]
readonly class GetUserGroupListQuery implements QueryInterface
{
    private function __construct(public Request $request) {}

    public static function create(Request $request): self
    {
        return new self($request);
    }
}
