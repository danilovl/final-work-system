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

namespace App\Application\Log;

use Monolog\Level;
use Symfony\Bridge\Monolog\Handler\ElasticsearchLogstashHandler;
use Symfony\Component\HttpClient\HttpClient;

class ElasticsearchLogstashHandlerFactory
{
    public static function create(
        string $endpoint,
        string $index,
        bool $bubble,
        string $elasticsearchVersion,
        string $elasticUsername,
        string $elasticPassword
    ): ElasticsearchLogstashHandler {
        return new ElasticsearchLogstashHandler(
            endpoint: $endpoint,
            index: $index,
            client: HttpClient::create(['timeout' => 1, 'auth_basic' => [$elasticUsername, $elasticPassword]]),
            level: Level::Debug,
            bubble: $bubble,
            elasticsearchVersion: $elasticsearchVersion
        );
    }
}
