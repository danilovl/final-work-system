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
        return mb_strtolower($search);
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
