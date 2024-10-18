<?php declare(strict_types=1);

namespace App\Tests\Unit\Application\Log;

use App\Application\Log\ElasticsearchLogstashHandlerFactory;
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
