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
    private function transformSearch(string $search): string
    {
        $search = mb_strtolower($search);

        return $this->removeAccents($search);
    }

    private function removeAccents(string $string): string
    {
        $transliterated = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $string);

        return $transliterated !== false ? $transliterated : $string;
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
