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

namespace App\Tests\Functional\Api\Domain\Security\Controller\Api;

use App\Domain\Security\Authenticator\ApiAuthenticator;
use App\Tests\Functional\Api\BaseApiTestCase;
use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\HttpFoundation\Request;

class SecurityControllerTest extends BaseApiTestCase
{
    public function testGenerateTokenAuthenticatesUser(): void
    {
        $data = $this->requestApi(Request::METHOD_GET, '/api/key/users/detail', self::SUPERVISOR);
        $this->assertResponseStatusCodeSame(200);

        $username = $this->getString($data, 'username');
        $this->assertSame(self::SUPERVISOR, $username);
    }

    public function testGenerateTokenReplacesPreviousToken(): void
    {
        $oldHeaders = $this->getAuthHeaders(self::STUDENT);

        $this->requestApi(
            method: Request::METHOD_POST,
            uri: '/api/public/security/generate-token',
            body: [
                'username' => self::STUDENT,
                'password' => 'studentstudent'
            ]
        );
        $this->assertResponseStatusCodeSame(200);

        $data = $this->requestApi(
            method: Request::METHOD_GET,
            uri: '/api/key/users/detail',
            headers: $oldHeaders
        );

        $this->assertResponseStatusCodeSame(403);

        $message = $this->getString($data, 'message');
        $this->assertSame('Username by token could not be found.', $message);
    }

    #[DataProvider('provideGenerateTokenFailedCases')]
    public function testGenerateTokenFailed(string $username, string $password): void
    {
        $data = $this->requestApi(
            method: Request::METHOD_POST,
            uri: '/api/public/security/generate-token',
            body: [
                'username' => $username,
                'password' => $password
            ]
        );

        $this->assertResponseStatusCodeSame(401);

        $message = $this->getString($data, 'message');
        $this->assertSame('Unauthorized', $message);
        $this->assertArrayNotHasKey('token', $data);
    }

    public function testMissingCredentialsUnauthorized(): void
    {
        $data = $this->requestApi(Request::METHOD_GET, '/api/key/users/detail');

        $this->assertResponseStatusCodeSame(401);

        $message = $this->getString($data, 'message');
        $this->assertSame('Unauthorized', $message);
    }

    public function testInvalidApiKeyUnauthorized(): void
    {
        $headers = $this->getAuthHeaders(self::SUPERVISOR);
        $apiKeyHeader = $this->toServerKey(ApiAuthenticator::AUTH_KEY);
        $headers[$apiKeyHeader] = 'invalid-api-key';

        $this->requestApi(
            method: Request::METHOD_GET,
            uri: '/api/key/users/detail',
            headers: $headers
        );

        $this->assertResponseStatusCodeSame(401);
    }

    public function testTokenOfAnotherUserForbidden(): void
    {
        $headers = $this->getAuthHeaders(self::SUPERVISOR);
        $usernameHeader = $this->toServerKey(ApiAuthenticator::AUTH_USER_USERNAME);
        $headers[$usernameHeader] = self::STUDENT;

        $data = $this->requestApi(
            method: Request::METHOD_GET,
            uri: '/api/key/users/detail',
            headers: $headers
        );

        $this->assertResponseStatusCodeSame(403);

        $message = $this->getString($data, 'message');
        $this->assertSame('Username by token could not be found.', $message);
    }

    public function testApiPlatformRequiresAuthentication(): void
    {
        $this->requestApi(Request::METHOD_GET, '/api-platform/task/list-owner');

        $this->assertResponseStatusCodeSame(401);
    }

    public static function provideGenerateTokenFailedCases(): Generator
    {
        yield 'wrong password' => ['supervisor', 'wrong-password'];
        yield 'unknown user' => ['unknown-api-test-user', 'password'];
        yield 'empty credentials' => ['', ''];
    }
}
