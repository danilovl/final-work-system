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

namespace App\Tests\Functional\Web\Test;

use App\Tests\Functional\Web\Test\Conversation\CreateConversationTest;
use App\Tests\Functional\Web\Test\Security\LoginTest;
use App\Tests\Functional\Web\Test\Task\CreateTaskTest;
use App\Tests\Functional\Web\Test\Work\CreateWorkTest;

/**
 * @group ignore
 */
class Web
{
    public function __construct(?array $argv)
    {
        if (isset($argv[1]) && $argv[1] === 'showEcho') {
            $this->printClasses();
        }
    }

    public function getTestClasses(): array
    {
        $classes = [
            LoginTest::class,
            CreateWorkTest::class,
            CreateConversationTest::class,
            CreateTaskTest::class
        ];

        foreach ($classes as &$path) {
            $path = str_replace('\\', '\\\\', $path);
        }

        return $classes;
    }

    public function printClasses(): void
    {
        echo implode("\n", $this->getTestClasses());
    }
}

/** @var array|null $arg */
$arg = $_SERVER['argv'];
new Web($arg);
