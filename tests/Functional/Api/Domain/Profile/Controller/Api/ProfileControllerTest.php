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

namespace App\Tests\Functional\Api\Domain\Profile\Controller\Api;

use App\Domain\User\Entity\User;
use App\Tests\Functional\Api\BaseApiTestCase;
use App\Tests\Functional\Web\Enum\LoginData;
use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\HttpFoundation\{
    BinaryFileResponse,
    Request
};

class ProfileControllerTest extends BaseApiTestCase
{
    private const string URI = '/api/key/users/password';

    private ?string $originalPasswordHash = null;

    private ?string $originalSalt = null;

    protected function tearDown(): void
    {
        $this->restoreStudentPassword();

        parent::tearDown();
    }

    public function testImage(): void
    {
        $studentId = $this->getUserId(self::STUDENT);
        $student = $this->getEntityManager()->find(User::class, $studentId);
        $this->assertNotNull($student);

        $profileImage = $student->getProfileImage();
        $uri = sprintf('/api/key/users/%d/profile/image', $studentId);

        $this->requestApi(Request::METHOD_GET, $uri, self::SUPERVISOR);

        $response = $this->client->getResponse();
        $content = (string) $response->getContent();

        if ($profileImage === null) {
            $this->assertResponseStatusCodeSame(204);
            $this->assertSame('', $content);

            return;
        }

        $this->assertResponseStatusCodeSame(200);
        $this->assertInstanceOf(BinaryFileResponse::class, $response);
    }

    public function testImageUserNotFound(): void
    {
        $data = $this->requestApi(Request::METHOD_GET, '/api/key/users/999999999/profile/image', self::SUPERVISOR);

        $this->assertResponseStatusCodeSame(404);

        $message = $this->getString($data, 'message');
        $this->assertSame('Resource not found', $message);
    }

    public function testChangePasswordSuccess(): void
    {
        $headers = $this->getAuthHeaders(self::STUDENT);
        $this->rememberStudentPassword();

        $newPassword = 'Api-' . bin2hex(random_bytes(6));

        $this->requestApi(
            method: Request::METHOD_PUT,
            uri: self::URI,
            username: self::STUDENT,
            body: [
                'currentPassword' => LoginData::STUDENT_PASSWORD->value,
                'newPassword' => $newPassword
            ]
        );

        $this->assertResponseStatusCodeSame(204);

        $content = (string) $this->client->getResponse()->getContent();
        $this->assertSame('', $content);

        $data = $this->requestApi(
            method: Request::METHOD_GET,
            uri: '/api/key/users/detail',
            headers: $headers
        );

        $this->assertResponseStatusCodeSame(200);

        $username = $this->getString($data, 'username');
        $this->assertSame(self::STUDENT, $username);

        $this->requestApi(
            method: Request::METHOD_POST,
            uri: '/api/public/security/generate-token',
            body: [
                'username' => self::STUDENT,
                'password' => LoginData::STUDENT_PASSWORD->value
            ]
        );

        $this->assertResponseStatusCodeSame(401);

        $data = $this->requestApi(
            method: Request::METHOD_POST,
            uri: '/api/public/security/generate-token',
            body: [
                'username' => self::STUDENT,
                'password' => $newPassword
            ]
        );

        $this->assertResponseStatusCodeSame(200);

        $token = $this->getString($data, 'token');
        $this->assertNotSame('', $token);
    }

    public function testChangePasswordInvalidCurrentPassword(): void
    {
        $this->rememberStudentPassword();

        $data = $this->requestApi(
            method: Request::METHOD_PUT,
            uri: self::URI,
            username: self::STUDENT,
            body: [
                'currentPassword' => 'wrong-' . bin2hex(random_bytes(4)),
                'newPassword' => 'new-password-123'
            ],
            headers: ['HTTP_ACCEPT_LANGUAGE' => 'en']
        );

        $this->assertResponseStatusCodeSame(400);

        $code = $this->getString($data, 'code');
        $message = $this->getString($data, 'message');

        $this->assertSame('invalid_current_password', $code);
        $this->assertSame('The current password is incorrect.', $message);
        $this->assertStudentPasswordUnchanged();
    }

    public function testChangePasswordInvalidCurrentPasswordTranslated(): void
    {
        $data = $this->requestApi(
            method: Request::METHOD_PUT,
            uri: self::URI,
            username: self::STUDENT,
            body: [
                'currentPassword' => 'wrong-' . bin2hex(random_bytes(4)),
                'newPassword' => 'new-password-123'
            ],
            headers: ['HTTP_ACCEPT_LANGUAGE' => 'cs']
        );

        $this->assertResponseStatusCodeSame(400);

        $code = $this->getString($data, 'code');
        $message = $this->getString($data, 'message');

        $this->assertSame('invalid_current_password', $code);
        $this->assertSame('Současné heslo není správné.', $message);
    }

    public function testChangePasswordInvalidNewPassword(): void
    {
        $this->rememberStudentPassword();

        $data = $this->requestApi(
            method: Request::METHOD_PUT,
            uri: self::URI,
            username: self::STUDENT,
            body: [
                'currentPassword' => LoginData::STUDENT_PASSWORD->value,
                'newPassword' => 'abc'
            ],
            headers: ['HTTP_ACCEPT_LANGUAGE' => 'en']
        );

        $this->assertResponseStatusCodeSame(422);

        $code = $this->getString($data, 'code');
        $message = $this->getString($data, 'message');

        $this->assertSame('invalid_new_password', $code);
        $this->assertSame('Your password should be at least 6 characters.', $message);
        $this->assertStudentPasswordUnchanged();
    }

    /**
     * @param array<string, mixed> $body
     */
    #[DataProvider('provideMissingFieldCases')]
    public function testChangePasswordMissingField(array $body): void
    {
        $data = $this->requestApi(
            method: Request::METHOD_PUT,
            uri: self::URI,
            username: self::STUDENT,
            body: $body
        );

        $this->assertResponseStatusCodeSame(400);

        $code = $this->getString($data, 'code');
        $message = $this->getString($data, 'message');

        $this->assertSame('missing_field', $code);
        $this->assertNotSame('', $message);
    }

    public function testChangePasswordMalformedJson(): void
    {
        $authHeaders = $this->getAuthHeaders(self::STUDENT);
        $server = array_merge(
            ['CONTENT_TYPE' => 'application/json', 'HTTP_ACCEPT' => 'application/json'],
            $authHeaders
        );

        $this->client->request(
            method: Request::METHOD_PUT,
            uri: self::URI,
            server: $server,
            content: '{"currentPassword": '
        );

        $this->assertResponseStatusCodeSame(400);

        $data = $this->getResponseData();
        $code = $this->getString($data, 'code');
        $message = $this->getString($data, 'message');

        $this->assertSame('invalid_request', $code);
        $this->assertNotSame('', $message);
    }

    public function testChangePasswordWithoutAuthentication(): void
    {
        $this->requestApi(
            method: Request::METHOD_PUT,
            uri: self::URI,
            body: [
                'currentPassword' => LoginData::STUDENT_PASSWORD->value,
                'newPassword' => 'new-password-123'
            ]
        );

        $this->assertResponseStatusCodeSame(401);
    }

    public function testChangePasswordMethodNotAllowed(): void
    {
        $this->requestApi(Request::METHOD_GET, self::URI, self::STUDENT);

        $this->assertResponseStatusCodeSame(405);
    }

    public static function provideMissingFieldCases(): Generator
    {
        yield 'empty body' => [[]];
        yield 'missing new password' => [['currentPassword' => 'secret']];
        yield 'missing current password' => [['newPassword' => 'new-password-123']];
        yield 'empty current password' => [['currentPassword' => '', 'newPassword' => 'new-password-123']];
        yield 'empty new password' => [['currentPassword' => 'secret', 'newPassword' => '']];
        yield 'non string value' => [['currentPassword' => 'secret', 'newPassword' => 123456]];
    }

    private function findStudent(): User
    {
        $entityManager = $this->getEntityManager();
        $entityManager->clear();

        /** @var User|null $user */
        $user = $entityManager
            ->getRepository(User::class)
            ->findOneBy(['username' => self::STUDENT]);

        $this->assertNotNull($user);

        return $user;
    }

    private function rememberStudentPassword(): void
    {
        $student = $this->findStudent();

        $this->originalPasswordHash = $student->getPassword();
        $this->originalSalt = $student->getSalt();
    }

    private function assertStudentPasswordUnchanged(): void
    {
        $student = $this->findStudent();
        $password = $student->getPassword();

        $this->assertSame($this->originalPasswordHash, $password);
    }

    private function restoreStudentPassword(): void
    {
        if ($this->originalPasswordHash === null) {
            return;
        }

        $student = $this->findStudent();
        $student->setPassword($this->originalPasswordHash);
        $student->setSalt($this->originalSalt);

        $this->getEntityManager()->flush();

        $this->originalPasswordHash = null;
        $this->originalSalt = null;
    }
}
