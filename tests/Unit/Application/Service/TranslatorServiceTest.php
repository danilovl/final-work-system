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

namespace App\Tests\Unit\Application\Service;

use App\Application\Service\TranslatorService;
use Generator;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class TranslatorServiceTest extends KernelTestCase
{
    private TranslatorService $translatorService;

    public function setUp(): void
    {
        $kernel = self::bootKernel();
        $this->translatorService = $kernel->getContainer()->get(TranslatorService::class);
    }

    /**
     * @dataProvider idProvider
     */
    public function testId(string $key, string $result): void
    {
        $trans = $this->translatorService->trans($key);
        $this->assertEquals($trans, $result);
    }

    /**
     * @dataProvider transProvider
     */
    public function testTrans(string $key, string $domain, string $locale, string $result): void
    {
        $trans = $this->translatorService->trans($key, [], $domain, $locale);
        $this->assertEquals($trans, $result);
    }

    public function idProvider(): Generator
    {
        yield ['app.system_name', 'System for conducting bachelor and diploma theses'];
        yield ['app.text.author', 'Author'];
        yield ['app.error.500', 'Internal Server Error'];
    }

    public function transProvider(): Generator
    {
        yield ['app.email_notification.subject.user_create', 'email_notification', 'en', 'Create a new account'];
        yield ['app.email_notification.subject.user_create', 'email_notification', 'ru', 'Создание новой учетной записи'];
        yield ['app.flash.form.save.success', 'flashes', 'en', 'Information is updated'];
        yield ['app.flash.form.save.success', 'flashes', 'ru', 'Информация обновлена'];
        yield ['app.flash.error.500', 'flashes', 'en', 'Internal Server Error'];
        yield ['app.flash.error.500', 'flashes', 'ru', 'Внутренняя ошибка сервера'];
        yield ['app.system_name', 'messages', 'en', 'System for conducting bachelor and diploma theses'];
        yield ['app.system_name', 'messages', 'ru', 'Система для ведения дипломных работ'];
        yield ['app.text.author', 'messages', 'en', 'Author'];
        yield ['app.text.author', 'messages', 'ru', 'Автор'];
        yield ['app.error.500', 'messages', 'en', 'Internal Server Error'];
        yield ['app.error.500', 'messages', 'ru', 'Внутренняя ошибка сервера'];
    }
}
