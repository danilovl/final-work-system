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

namespace App\Domain\Work\DTO\Api\Input;

use Symfony\Component\Validator\Constraints as Assert;

readonly class WorkSearchInput
{
    /**
     * @param int[]|int|string|null $status
     * @param int[]|int|string|null $type
     * @param int[]|int|string|null $author
     * @param int[]|int|string|null $supervisor
     * @param int[]|int|string|null $opponent
     * @param int[]|int|string|null $consultant
     * @param string[]|string|null $deadline
     */
    public function __construct(
        public ?string $title = null,
        public ?string $shortcut = null,
        public int|string|array|null $status = null,
        public int|string|array|null $type = null,
        public int|string|array|null $author = null,
        public int|string|array|null $supervisor = null,
        public int|string|array|null $opponent = null,
        public int|string|array|null $consultant = null,
        public string|array|null $deadline = null,
        #[Assert\Positive]
        public ?int $page = null,
        #[Assert\Positive]
        public ?int $limit = null,
        public ?string $search = null
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $title = $this->title ?? $this->search;
        if ($title !== null) {
            $title = mb_trim($title);
        }
        $shortcut = $this->shortcut !== null ? mb_trim($this->shortcut) : null;

        $data = [
            'title' => $title,
            'shortcut' => $shortcut,
            'status' => $this->normalizeArray($this->status),
            'type' => $this->normalizeArray($this->type),
            'author' => $this->normalizeArray($this->author),
            'supervisor' => $this->normalizeArray($this->supervisor),
            'opponent' => $this->normalizeArray($this->opponent),
            'consultant' => $this->normalizeArray($this->consultant),
            'deadline' => $this->normalizeArray($this->deadline)
        ];

        return array_filter($data, static fn (mixed $value): bool => $value !== null && $value !== [] && $value !== '');
    }

    /**
     * @param int|string|array<array-key, mixed>|null $value
     * @return array<array-key, mixed>|null
     */
    private function normalizeArray(int|string|array|null $value): ?array
    {
        if ($value === null) {
            return null;
        }

        return is_array($value) ? array_values($value) : [$value];
    }
}
