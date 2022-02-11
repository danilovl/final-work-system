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

namespace App\Tests\Application\Helper;

use PHPUnit\Framework\TestCase;

class FileHelperTest extends TestCase
{
    public function testCreateTmpFile(): void
    {
        $fileContent = '<?php echo "test" ?>';
        $filePath = \App\Application\Helper\FileHelper::createTmpFile('php', $fileContent);

        $this->assertEquals($fileContent, file_get_contents($filePath));
    }
}
