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

namespace App\Tests\Unit\Application\Menu;

use App\Application\Exception\InvalidArgumentException;
use App\Application\Menu\MenuItem;
use PHPUnit\Framework\TestCase;

class MenuItemTest extends TestCase
{
    public function testBasicMenuItemBehavior(): void
    {
        $item = new MenuItem('home');

        $this->assertEquals('home', $item->getName());
        $this->assertEquals('home', $item->getLabel());
        $this->assertTrue($item->isDisplayed());

        $item->setLabel('Home Page');
        $this->assertEquals('Home Page', $item->getLabel());

        $item->setIsDisplayed(false);
        $this->assertFalse($item->isDisplayed());
    }

    public function testMenuItemWithChildren(): void
    {
        $item = new MenuItem('home');

        $child1 = new MenuItem('about');
        $child1->setLabel('About Us');
        $child1->setUri('/about');

        $child2 = new MenuItem('contact');
        $child2->setLabel('Contact Us');
        $child2->setUri('/contact');

        $item->addChild($child1);
        $item->addChild($child2);

        $this->assertCount(2, $item->getChildren());

        $this->assertSame($child1, $item->getChild('about'));
        $this->assertSame($child2, $item->getChild('contact'));

        $this->assertSame($item, $child1->getParent());
        $this->assertSame($item, $child2->getParent());
    }

    public function testRenameMenuItem(): void
    {
        $item1 = new MenuItem('home');
        $item2 = new MenuItem('about');
        $item2->setParent($item1);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot rename item, name is already used by sibling.');

        $item2->setName('home');
    }

    public function testMenuItemAttributes(): void
    {
        $item = new MenuItem('home');
        $item->setAttributes([
            'class' => 'menu-item',
            'data-id' => 123
        ]);

        $this->assertEquals(['class' => 'menu-item', 'data-id' => 123], $item->getAttributes());

        $item->addAttribute('data-type', 'page');
        $this->assertEquals(['class' => 'menu-item', 'data-id' => 123, 'data-type' => 'page'], $item->getAttributes());

        $this->assertSame('menu-item', $item->getAttribute('class'));
        $this->assertSame(123, $item->getAttribute('data-id'));
        $this->assertSame('page', $item->getAttribute('data-type'));

        $item->setAttributes([]);
        $this->assertEquals([], $item->getAttributes());
    }
}
