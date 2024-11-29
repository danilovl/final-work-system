<?php declare(strict_types=1);

/*
 *
 * This file is part of the FinalWorkSystem project.
 * (c) Vladimir Danilov
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace App\Tests\Unit\Application\Helper;

use App\Application\Helper\FileHelper;
use PHPUnit\Framework\TestCase;

class FileHelperTest extends TestCase
{
    private string $testDir;

    protected function setUp(): void
    {
        $this->testDir = __DIR__ . '/testDir';
        mkdir($this->testDir);

        file_put_contents($this->testDir . '/file2.txt', 'File 2');
        mkdir($this->testDir . '/subDir');
        file_put_contents($this->testDir . '/subDir/file3.txt', 'File 3');
    }

    protected function tearDown(): void
    {
        if (file_exists($this->testDir)) {
            FileHelper::deleteDirectory($this->testDir);
        }
    }

    public function testCreateTmpFile(): void
    {
        $fileContent = '<?php echo "test" ?>';
        $filePath = FileHelper::createTmpFile('php', $fileContent, 'prefix');

        $this->assertEquals($fileContent, file_get_contents($filePath));
    }

    public function testDeleteDirectory(): void
    {
        $this->assertDirectoryExists($this->testDir);

        $file = $this->testDir . '/file1.txt';
        file_put_contents($file, 'File 1');
        $result = FileHelper::deleteDirectory($file);
        $this->assertTrue($result);

        $result = FileHelper::deleteDirectory($this->testDir);

        $this->assertTrue($result);
        $this->assertDirectoryDoesNotExist($this->testDir);

        $result = FileHelper::deleteDirectory($this->testDir);
        $this->assertFalse($result);
    }
}
