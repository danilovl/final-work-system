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

namespace App\Tests\Functional\Api\Infrastructure\Api;

use App\Tests\Functional\Api\BaseApiTestCase;
use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\HttpFoundation\Request;

class ApiDocTest extends BaseApiTestCase
{
    #[DataProvider('provideDocContainsPathCases')]
    public function testDocContainsPath(string $path, string $method): void
    {
        $data = $this->requestApi(Request::METHOD_GET, '/api/public/doc.json');

        $this->assertResponseStatusCodeSame(200);

        $openapi = $this->getString($data, 'openapi');
        $operation = $this->getArray($data, 'paths', $path, $method);
        $responses = $this->getArray($operation, 'responses');

        $this->assertStringStartsWith('3.', $openapi);
        $this->assertNotEmpty($responses);
    }

    public static function provideDocContainsPathCases(): Generator
    {
        yield 'work list' => ['/api/key/works/{type}', 'get'];
        yield 'task create' => ['/api/key/tasks/create', 'post'];
        yield 'conversation message create' => ['/api/key/conversations/{id}/message', 'post'];
        yield 'calendar event' => ['/api/key/events/calendar/{type}', 'get'];
    }
}
