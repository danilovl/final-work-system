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

namespace App\Tests\Functional\Web\Test\Security;

use App\Tests\Functional\Web\Enum\LoginData;
use App\Tests\Functional\Web\Traits\LoginTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class LoginTest extends WebTestCase
{
    use LoginTrait;

    public function testSLogin(): void
    {
        $client = static::createClient();

        $this->login(
            client: $client,
            username: LoginData::STUDENT_USERNAME->value,
            password: LoginData::STUDENT_PASSWORD->value
        );

        $this->assertEquals('/', $client->getRequest()->getPathInfo());
    }
}
