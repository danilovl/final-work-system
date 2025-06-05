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
    public function testSetName(): void
    {
        $menuItem = new MenuItem('home');
        $this->assertSame('home', $menuItem->getName());

        $menuItem->setName('new')->setName('new');
        $this->assertSame('new', $menuItem->getName());

        $menuItemParent = new MenuItem('static');
        $menuItem->setParent($menuItemParent);
        $menuItem->setName('parent');

        $menuItemParent = new MenuItem('parent');
        $menuItemParent->setChildren([
            'home' => new MenuItem('home'),
            'child' => new MenuItem('child'),
        ]);

        $menuItem->setParent($menuItemParent);
        $menuItem->setName('home');

        $this->expectException(InvalidArgumentException::class);
        $menuItem->setName('parent');
    }

    public function testGetUri(): void
    {
        $menuItem = new MenuItem('home');
        $this->assertNull($menuItem->getUri());

        $menuItem->setUri('new');
        $this->assertSame('new', $menuItem->getUri());
    }

    public function testBasicMenuItemBehavior(): void
    {
        $menuItem = new MenuItem('home');

        $this->assertEquals('home', $menuItem->getName());
        $this->assertEquals('home', $menuItem->getLabel());
        $this->assertTrue($menuItem->isDisplayed());

        $menuItem->setLabel('Home Page');
        $this->assertEquals('Home Page', $menuItem->getLabel());

        $menuItem->setIsDisplayed(false);
        $this->assertFalse($menuItem->isDisplayed());
    }

    public function testMenuItemWithChildren(): void
    {
        $menuItem = new MenuItem('home');

        $childMenuItemOne = new MenuItem('about');
        $childMenuItemOne->setLabel('About Us');
        $childMenuItemOne->setUri('/about');

        $childMenuItemTwo = new MenuItem('contact');
        $childMenuItemTwo->setLabel('Contact Us');
        $childMenuItemTwo->setUri('/contact');

        $menuItem->addChild($childMenuItemOne);
        $menuItem->addChild($childMenuItemTwo);

        $this->assertCount(2, $menuItem->getChildren());

        $this->assertSame($childMenuItemOne, $menuItem->getChild('about'));
        $this->assertSame($childMenuItemTwo, $menuItem->getChild('contact'));

        $this->assertSame($menuItem, $childMenuItemOne->getParent());
        $this->assertSame($menuItem, $childMenuItemTwo->getParent());
    }

    public function testRenameMenuItem(): void
    {
        $menuItemOne = new MenuItem('home');
        $menuItemTwo = new MenuItem('about');
        $menuItemTwo->setParent($menuItemOne);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot rename item, name is already used by sibling.');

        $menuItemTwo->setName('home');
    }

    public function testMenuItemAttributes(): void
    {
        $menuItem = new MenuItem('home');
        $menuItem->setAttributes([
            'class' => 'menu-item',
            'data-id' => 123
        ]);

        $this->assertEquals(['class' => 'menu-item', 'data-id' => 123], $menuItem->getAttributes());

        $menuItem->addAttribute('data-type', 'page');
        $this->assertEquals(['class' => 'menu-item', 'data-id' => 123, 'data-type' => 'page'], $menuItem->getAttributes());

        $this->assertSame('menu-item', $menuItem->getAttribute('class'));
        $this->assertSame(123, $menuItem->getAttribute('data-id'));
        $this->assertSame('page', $menuItem->getAttribute('data-type'));

        $menuItem->setAttributes([]);
        $this->assertEquals([], $menuItem->getAttributes());
    }

    public function testSetChildren(): void
    {
        $menuItem = new MenuItem('home');
        $menuItem->setChildren([]);

        $this->assertSame([], $menuItem->getChildren());
    }

    public function testAddChildren(): void
    {
        $menuItem = new MenuItem('home');
        $menuItemChildren = new MenuItem('children');
        $menuItem->addChildren($menuItemChildren);

        $this->assertSame([$menuItemChildren], $menuItem->getChildren());
    }

    public function testRemoveChild(): void
    {
        $menuItem = new MenuItem('home');
        $menuItemChild = new MenuItem('child');

        $menuItem->addChild($menuItemChild);
        $this->assertSame($menuItemChild, $menuItem->getChild('child'));

        $menuItem->removeChild('child');
        $this->assertNull($menuItem->getChild('child'));
    }

    public function testSetParent(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $menuItem = new MenuItem('home');
        $menuItem->setParent($menuItem);

        $menuItem = new MenuItem('home');
        $menuItem->setParent(new MenuItem('parent'));
    }
}
