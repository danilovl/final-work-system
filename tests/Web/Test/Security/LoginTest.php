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
