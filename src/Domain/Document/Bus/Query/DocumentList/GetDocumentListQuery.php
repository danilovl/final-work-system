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
     * @param User[] $users
     * @param array<string, mixed>|null $criteria
     */
    private function __construct(
        public Request $request,
        public array $users,
        public ?array $criteria,
        public bool $detachEntity = false,
        public ?bool $active = null,
    ) {}

    /**
     * @param User[] $users
     * @param array<string, mixed>|null $criteria
     */
    public static function create(
        Request $request,
        array $users,
        ?array $criteria,
        bool $detachEntity = false,
        ?bool $active = null,
    ): self {
        return new self($request, $users, $criteria, $detachEntity, $active);
    }
}
