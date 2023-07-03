<?php declare(strict_types=1);

namespace App\Tests\Web\Test\Security;

use App\Tests\Web\Enum\LoginData;
use App\Tests\Web\Traits\LoginTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class LoginTest extends WebTestCase
{
    use LoginTrait;

    public function testSLogin(): void
    {
        $client = static::createClient();

        $this->login(
            $client,
            LoginData::STUDENT_USERNAME->value,
            LoginData::STUDENT_PASSWORD->value
        );

        $this->assertEquals('/', $client->getRequest()->getPathInfo());
    }
}
