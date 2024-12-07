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

namespace App\Tests\Helper\Application\Service;

use Aws\Result;

class S3Client extends \Aws\S3\S3Client
{
    public function getObject(array $args = []): Result
    {
        return new Result;
    }

    public function createBucket(array $args = []): Result
    {
        return new Result;
    }

    public function putObject(array $args = []): Result
    {
        return new Result;
    }

    public function deleteObject(array $args = []): Result
    {
        return new Result;
    }
}
