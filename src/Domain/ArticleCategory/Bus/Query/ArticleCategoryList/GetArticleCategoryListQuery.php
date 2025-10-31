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

namespace App\Domain\ArticleCategory\Bus\Query\ArticleCategoryList;

use App\Application\Interfaces\Bus\QueryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage('sync')]
readonly class GetArticleCategoryListQuery implements QueryInterface
{
    /**
     * @param string[] $roles
     */
    private function __construct(
        public Request $request,
        public array $roles
    ) {}

    /**
     * @param string[] $roles
     */
    public static function create(Request $request, array $roles): self
    {
        return new self(
            request: $request,
            roles: $roles
        );
    }
}
