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

namespace App\Tests\Unit\Application\Helper;

use App\Application\Exception\RuntimeException;
use App\Application\Helper\AttributeHelper;
use Doctrine\ORM\Mapping as ORM;
use PHPUnit\Framework\TestCase;

class AttributeHelperTest extends TestCase
{
    public function testGetEntityTableName(): void
    {
        $entity = new #[ORM\Table(name: 'test_table')] #[ORM\Entity] class {};

        $this->assertSame('test_table', AttributeHelper::getEntityTableName($entity::class));
    }

    public function testGetEntityTableNameException(): void
    {
        $entity = new #[ORM\Entity] class ( ) {};

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Attribute Doctrine\ORM\Mapping\Table not found.');

        AttributeHelper::getEntityTableName($entity::class);
    }
}
