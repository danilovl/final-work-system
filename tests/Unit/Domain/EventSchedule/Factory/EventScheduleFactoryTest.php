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

namespace App\Tests\Unit\Domain\EventSchedule\Factory;

use App\Domain\EventSchedule\Entity\EventSchedule;
use App\Domain\EventSchedule\Factory\EventScheduleFactory;
use App\Domain\EventSchedule\Model\EventScheduleModel;
use App\Domain\EventScheduleTemplate\Entity\EventScheduleTemplate;
use App\Domain\EventScheduleTemplate\Factory\EventScheduleTemplateFactory;
use App\Domain\EventScheduleTemplate\Model\EventScheduleTemplateModel;
use App\Domain\EventType\Entity\EventType;
use App\Domain\User\Entity\User;
use App\Infrastructure\Service\EntityManagerService;
use DateTime;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class EventScheduleFactoryTest extends TestCase
{
    private EntityManagerService&MockObject $entityManagerService;

    private EventScheduleFactory $eventScheduleFactory;

    protected function setUp(): void
    {
        $this->entityManagerService = $this->createMock(EntityManagerService::class);
        $this->eventScheduleFactory = new EventScheduleFactory(
            $this->entityManagerService,
            new EventScheduleTemplateFactory($this->entityManagerService)
        );
    }

    public function testNewTemplatesGetSchedule(): void
    {
        $model = $this->createScheduleModel();
        $model->templates->add($this->createTemplateModel(null, 'First', 1));
        $model->templates->add($this->createTemplateModel(null, 'Second', 2));

        $this->entityManagerService
            ->expects($this->never())
            ->method('remove');

        $eventSchedule = $this->eventScheduleFactory->fromModel(new EventSchedule, $model);

        $templates = $this->templates($eventSchedule);
        $this->assertCount(2, $templates);
        foreach ($templates as $template) {
            $this->assertSame($eventSchedule, $template->getSchedule());
        }
        $this->assertSame('First', $templates[0]->getName());
    }

    public function testExistingTemplatesAreUpdatedAddedAndRemoved(): void
    {
        $eventSchedule = new EventSchedule;
        $kept = $this->createTemplate(1, 'Kept');
        $removed = $this->createTemplate(2, 'Removed');
        $eventSchedule->addTemplate($kept);
        $eventSchedule->addTemplate($removed);

        $model = EventScheduleModel::fromEventSchedule($this->withOwner($eventSchedule));
        $this->assertCount(2, $model->templates);
        $this->assertNotContains($kept, $model->templates->toArray(), 'form must work with models, not entities');

        $keptModel = $model->templates->get(0);
        $this->assertInstanceOf(EventScheduleTemplateModel::class, $keptModel);
        $keptModel->name = 'Kept edited';
        $model->templates->remove(1);
        $model->templates->add($this->createTemplateModel(null, 'Added', 4));

        $this->entityManagerService
            ->expects($this->once())
            ->method('remove')
            ->with($removed);

        $this->eventScheduleFactory->fromModel($eventSchedule, $model);

        $templates = $this->templates($eventSchedule);
        $this->assertSame(['Kept edited', 'Added'], [$templates[0]->getName(), $templates[1]->getName()]);
        $this->assertSame($kept, $templates[0]);
        $this->assertSame($eventSchedule, $templates[1]->getSchedule());
    }

    /**
     * @return list<EventScheduleTemplate>
     */
    private function templates(EventSchedule $eventSchedule): array
    {
        $templates = [];
        foreach ($eventSchedule->getTemplates() as $template) {
            $this->assertInstanceOf(EventScheduleTemplate::class, $template);
            $templates[] = $template;
        }

        return $templates;
    }

    private function createScheduleModel(): EventScheduleModel
    {
        $model = new EventScheduleModel;
        $model->name = 'Schedule';
        $model->owner = new User;

        return $model;
    }

    private function withOwner(EventSchedule $eventSchedule): EventSchedule
    {
        $eventSchedule->setId(1);
        $eventSchedule->setName('Schedule');
        $eventSchedule->setOwner(new User);

        return $eventSchedule;
    }

    private function createTemplate(int $id, string $name): EventScheduleTemplate
    {
        $template = new EventScheduleTemplate;
        $template->setId($id);
        $template->setName($name);
        $template->setType(new EventType);
        $template->setDay(1);
        $template->setStart(new DateTime('1970-01-01 09:00'));
        $template->setEnd(new DateTime('1970-01-01 10:00'));

        return $template;
    }

    private function createTemplateModel(?int $id, string $name, int $day): EventScheduleTemplateModel
    {
        $model = new EventScheduleTemplateModel;
        $model->id = $id;
        $model->type = new EventType;
        $model->day = $day;
        $model->name = $name;
        $model->start = new DateTime('1970-01-01 09:00');
        $model->end = new DateTime('1970-01-01 10:00');

        return $model;
    }
}
