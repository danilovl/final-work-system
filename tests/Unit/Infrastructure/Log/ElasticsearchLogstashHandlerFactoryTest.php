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

namespace App\Tests\Unit\Infrastructure\Log;

use App\Infrastructure\Log\ElasticsearchLogstashHandlerFactory;
use Monolog\Level;
use PHPUnit\Framework\TestCase;
use Symfony\Bridge\Monolog\Handler\ElasticsearchLogstashHandler;
use Symfony\Component\HttpClient\HttpClient;

class ElasticsearchLogstashHandlerFactoryTest extends TestCase
{
    public function testCreate(): void
    {
        $endpoint = 'http://example.com';
        $index = 'logs';
        $bubble = true;
        $elasticsearchVersion = '7.0';
        $elasticUsername = 'username';
        $elasticPassword = 'password';

        $exceptedHandler = new ElasticsearchLogstashHandler(
            endpoint: $endpoint,
            index: $index,
            client: HttpClient::create(['timeout' => 1, 'auth_basic' => [$elasticUsername, $elasticPassword]]),
            level: Level::Error,
            bubble: $bubble,
            elasticsearchVersion: $elasticsearchVersion
        );

        $handler = ElasticsearchLogstashHandlerFactory::create(
            $endpoint,
            $index,
            $bubble,
            $elasticsearchVersion,
            $elasticUsername,
            $elasticPassword
        );

        $this->assertEquals($exceptedHandler, $handler);
    }
}
