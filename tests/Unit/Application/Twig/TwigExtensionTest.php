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

namespace App\Tests\Unit\Application\Twig;

use App\Application\Twig\TwigExtension;
use PHPUnit\Framework\TestCase;
use Twig\{
    TwigFilter,
    TwigFunction
};

class TwigExtensionTest extends TestCase
{
    public function testGetFunctions(): void
    {
        $seoExtension = new TwigExtension;
        $twigFunction = array_map(static function (TwigFunction $twigFunction): string {
            return $twigFunction->getName();
        }, $seoExtension->getFunctions());

        $this->assertEquals(
            [
                'app_user', 'is_user_role',
                'is_work_role', 'work_deadline_days', 'work_deadline_program_days',
                'task_work_complete_percentage',
                'check_work_users_conversation', 'conversation_last_message', 'conversation_message_read_date_recipient',
                'system_event_generate_link',
                'locales'
            ],
            $twigFunction
        );
    }

    public function testGetFilters(): void
    {
        $twigExtension = new TwigExtension;
        $twigFilters = array_map(static function (TwigFilter $twigFilter): string {
            return $twigFilter->getName();
        }, $twigExtension->getFilters());

        $this->assertEquals(
            ['away_to', 'profile_image'],
            $twigFilters
        );
    }
}
