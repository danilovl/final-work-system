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

namespace App\Application\Traits\Repository\Elastica;

use Elastica\Result;
use Webmozart\Assert\Assert;

trait ElasticaSearchTrait
{
    /**
     * @return array{wildcard: array<string, string>}
     */
    private function createContainsQuery(string $field, string $search): array
    {
        $transformedSearch = $this->transformSearch($search);
        $escapedSearch = addcslashes($transformedSearch, '\\*?');
        $pattern = sprintf('*%s*', $escapedSearch);

        return ['wildcard' => [$field => $pattern]];
    }

    private function transformSearch(string $search): string
    {
        $search = mb_strtolower($search);

        return $this->removeAccents($search);
    }

    private function removeAccents(string $string): string
    {
        if (preg_match('~\p{Cyrillic}~u', $string)) {
            return $string;
        }

        $transliterated = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $string);

        return $transliterated !== false ? $transliterated : $string;
    }

    /**
     * @param callable(array<string, mixed>): Result[] $search
     * @param array<string, mixed> $query
     * @return Result[]
     */
    private function findAllRaw(callable $search, array $query): array
    {
        $query['sort'] = [['id' => 'asc']];
        /** @var int $pageSize */
        $pageSize = $query['size'];
        $results = [];

        do {
            $pageResults = $search($query);
            array_push($results, ...$pageResults);

            $lastResult = end($pageResults);
            if ($lastResult === false) {
                break;
            }

            $query['search_after'] = $lastResult->getSort();
            $isFullPage = count($pageResults) === $pageSize;
        } while ($isFullPage);

        return $results;
    }

    /**
     * @param Result[] $results
     * @return int[]
     */
    private function getDocumentIds(array $results): array
    {
        Assert::allIsInstanceOf($results, Result::class);

        return array_map(static fn (Result $document): int => (int) $document->getId(), $results);
    }
}
