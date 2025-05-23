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

namespace App\Tests\Mock\Application\Traits\Repository\Elastica;

use App\Application\Traits\Repository\Elastica\ElasticaSearchTrait;

class ElasticaSearchMock
{
    use ElasticaSearchTrait;

    public function testTransformSearch(string $search): string
    {
        return $this->transformSearch($search);
    }

    public function testGetDocumentIds(array $results): array
    {
        return $this->getDocumentIds($results);
    }
}
