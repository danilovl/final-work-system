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
use Symfony\Component\HttpFoundation\{
    BinaryFileResponse,
    Request
};

class ProfileControllerTest extends BaseApiTestCase
{
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
}
