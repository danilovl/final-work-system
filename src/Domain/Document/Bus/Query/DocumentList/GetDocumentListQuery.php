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

namespace App\Domain\Document\Bus\Query\DocumentList;

use App\Application\Interfaces\Bus\QueryInterface;
use App\Domain\User\Entity\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage('sync')]
readonly class GetDocumentListQuery implements QueryInterface
{
    /**
     * @param array<string, mixed> $criteria
     */
    private function __construct(
        public Request $request,
        public User $user,
        public ?array $criteria,
    ) {}

    /**
     * @param array<string, mixed> $criteria
     */
    public static function create(
        Request $request,
        User $user,
        ?array $criteria,
    ): self {
        return new self($request, $user, $criteria);
    }
}
