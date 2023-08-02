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

namespace App\Tests\Web\Test;

use App\Tests\Web\Test\Conversation\CreateConversationTest;
use App\Tests\Web\Test\Security\LoginTest;
use App\Tests\Web\Test\Task\CreateTaskTest;
use App\Tests\Web\Test\Work\CreateWorkTest;

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

new Web($_SERVER['argv']);
