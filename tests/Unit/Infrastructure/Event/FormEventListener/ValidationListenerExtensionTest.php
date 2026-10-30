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

namespace App\Tests\Unit\Infrastructure\Event\FormEventListener;

use App\Application\Constant\FormOperationTypeConstant;
use App\Application\EventDispatcher\RequestFlashEventDispatcher;
use App\Infrastructure\Event\FormEventListener\ValidationListenerExtension;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use stdClass;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\{
    FormBuilderInterface,
    FormConfigInterface,
    FormEvent,
    FormInterface
};
use Symfony\Component\OptionsResolver\OptionsResolver;

class ValidationListenerExtensionTest extends TestCase
{
    private MockObject&RequestFlashEventDispatcher $requestFlashEventDispatcher;

    private ValidationListenerExtension $validationListenerExtension;

    protected function setUp(): void
    {
        $this->requestFlashEventDispatcher = $this->createMock(RequestFlashEventDispatcher::class);
        $this->validationListenerExtension = new ValidationListenerExtension($this->requestFlashEventDispatcher);
    }

    public function testGetExtendedTypes(): void
    {
        $extendedTypes = ValidationListenerExtension::getExtendedTypes();

        $types = iterator_to_array($extendedTypes);
        $this->assertCount(1, $types);
        $this->assertSame(FormType::class, $types[0]);
    }

    public function testConfigureOptions(): void
    {
        $resolver = $this->createMock(OptionsResolver::class);

        $resolver->expects($this->once())
            ->method('setDefined')
            ->with(FormOperationTypeConstant::OPTION_KEY)
            ->willReturnSelf();

        $resolver->expects($this->once())
            ->method('setDefault')
            ->with(FormOperationTypeConstant::OPTION_KEY, null)
            ->willReturnSelf();

        $resolver->expects($this->once())
            ->method('setAllowedTypes')
            ->with(FormOperationTypeConstant::OPTION_KEY, [FormOperationTypeConstant::class, 'null'])
            ->willReturnSelf();

        $this->validationListenerExtension->configureOptions($resolver);
    }

    public function testBuildFormWithNullDataClass(): void
    {
        $builder = $this->createMock(FormBuilderInterface::class);

        $builder->expects($this->once())
            ->method('getDataClass')
            ->willReturn(null);

        $builder->expects($this->never())
            ->method('addEventListener');

        $this->validationListenerExtension->buildForm($builder, []);
    }

    public function testBuildFormWithDataClass(): void
    {
        $builder = $this->createMock(FormBuilderInterface::class);

        $builder->expects($this->once())
            ->method('getDataClass')
            ->willReturn(stdClass::class);

        $builder->expects($this->once())
            ->method('addEventListener')
            ->with(
                $this->equalTo('form.post_submit'),
                $this->callback(static function ($callback) {
                    return is_callable($callback);
                }),
                $this->equalTo(-100)
            )
            ->willReturn($builder);

        $this->validationListenerExtension->buildForm($builder, []);
    }

    public function testOnPostSubmitWithValidForm(): void
    {
        $form = $this->createMock(FormInterface::class);
        $event = $this->createMock(FormEvent::class);

        $event->expects($this->once())
            ->method('getForm')
            ->willReturn($form);

        $form->expects($this->once())
            ->method('isSubmitted')
            ->willReturn(true);

        $form->expects($this->once())
            ->method('isValid')
            ->willReturn(true);

        $this->requestFlashEventDispatcher
            ->expects($this->never())
            ->method($this->anything());

        $this->simulatePostSubmitEvent($event);
    }

    public function testOnPostSubmitWithInvalidFormAndCreateOperationType(): void
    {
        $form = $this->createMock(FormInterface::class);
        $formConfig = $this->createMock(FormConfigInterface::class);
        $event = $this->createMock(FormEvent::class);

        $event->expects($this->once())
            ->method('getForm')
            ->willReturn($form);

        $form->expects($this->once())
            ->method('isSubmitted')
            ->willReturn(true);

        $form->expects($this->once())
            ->method('isValid')
            ->willReturn(false);

        $form->expects($this->once())
            ->method('getConfig')
            ->willReturn($formConfig);

        $formConfig->expects($this->once())
            ->method('getOption')
            ->with(FormOperationTypeConstant::OPTION_KEY)
            ->willReturn(FormOperationTypeConstant::CREATE);

        $this->requestFlashEventDispatcher
            ->expects($this->once())
            ->method('onCreateFailure');

        $this->simulatePostSubmitEvent($event);
    }

    public function testOnPostSubmitWithInvalidFormAndEditOperationType(): void
    {
        $form = $this->createMock(FormInterface::class);
        $formConfig = $this->createMock(FormConfigInterface::class);
        $event = $this->createMock(FormEvent::class);

        $event->expects($this->once())
            ->method('getForm')
            ->willReturn($form);

        $form->expects($this->once())
            ->method('isSubmitted')
            ->willReturn(true);

        $form->expects($this->once())
            ->method('isValid')
            ->willReturn(false);

        $form->expects($this->once())
            ->method('getConfig')
            ->willReturn($formConfig);

        $formConfig->expects($this->once())
            ->method('getOption')
            ->with(FormOperationTypeConstant::OPTION_KEY)
            ->willReturn(FormOperationTypeConstant::EDIT);

        $this->requestFlashEventDispatcher
            ->expects($this->once())
            ->method('onSaveFailure');

        $this->simulatePostSubmitEvent($event);
    }

    public function testOnPostSubmitWithInvalidFormAndDeleteOperationType(): void
    {
        $form = $this->createMock(FormInterface::class);
        $formConfig = $this->createMock(FormConfigInterface::class);
        $event = $this->createMock(FormEvent::class);

        $event->expects($this->once())
            ->method('getForm')
            ->willReturn($form);

        $form->expects($this->once())
            ->method('isSubmitted')
            ->willReturn(true);

        $form->expects($this->once())
            ->method('isValid')
            ->willReturn(false);

        $form->expects($this->once())
            ->method('getConfig')
            ->willReturn($formConfig);

        $formConfig->expects($this->once())
            ->method('getOption')
            ->with(FormOperationTypeConstant::OPTION_KEY)
            ->willReturn(FormOperationTypeConstant::DELETE);

        $this->requestFlashEventDispatcher
            ->expects($this->once())
            ->method('onRemoveFailure');

        $this->simulatePostSubmitEvent($event);
    }

    public function testOnPostSubmitWithInvalidFormAndNoOperationTypeWithNullId(): void
    {
        $form = $this->createMock(FormInterface::class);
        $formConfig = $this->createMock(FormConfigInterface::class);
        $event = $this->createMock(FormEvent::class);
        $object = new class() {
            public ?int $id = null;
        };

        $event->expects($this->once())
            ->method('getForm')
            ->willReturn($form);

        $form->expects($this->once())
            ->method('isSubmitted')
            ->willReturn(true);

        $form->expects($this->once())
            ->method('isValid')
            ->willReturn(false);

        $form->expects($this->once())
            ->method('getConfig')
            ->willReturn($formConfig);

        $formConfig->expects($this->once())
            ->method('getOption')
            ->with(FormOperationTypeConstant::OPTION_KEY)
            ->willReturn(null);

        $form->expects($this->once())
            ->method('getData')
            ->willReturn($object);

        $this->requestFlashEventDispatcher
            ->expects($this->once())
            ->method('onCreateFailure');

        $this->simulatePostSubmitEvent($event);
    }

    public function testOnPostSubmitWithInvalidFormAndNoOperationTypeWithId(): void
    {
        $form = $this->createMock(FormInterface::class);
        $formConfig = $this->createMock(FormConfigInterface::class);
        $event = $this->createMock(FormEvent::class);
        $object = new class() {
            public ?int $id = 1;
        };

        $event->expects($this->once())
            ->method('getForm')
            ->willReturn($form);

        $form->expects($this->once())
            ->method('isSubmitted')
            ->willReturn(true);

        $form->expects($this->once())
            ->method('isValid')
            ->willReturn(false);

        $form->expects($this->once())
            ->method('getConfig')
            ->willReturn($formConfig);

        $formConfig->expects($this->once())
            ->method('getOption')
            ->with(FormOperationTypeConstant::OPTION_KEY)
            ->willReturn(null);

        $form->expects($this->once())
            ->method('getData')
            ->willReturn($object);

        $this->requestFlashEventDispatcher
            ->expects($this->once())
            ->method('onSaveFailure');

        $this->simulatePostSubmitEvent($event);
    }

    public function testOnPostSubmitWithInvalidFormAndNoOperationTypeWithNonObjectData(): void
    {
        $form = $this->createMock(FormInterface::class);
        $formConfig = $this->createMock(FormConfigInterface::class);
        $event = $this->createMock(FormEvent::class);

        $event->expects($this->once())
            ->method('getForm')
            ->willReturn($form);

        $form->expects($this->once())
            ->method('isSubmitted')
            ->willReturn(true);

        $form->expects($this->once())
            ->method('isValid')
            ->willReturn(false);

        $form->expects($this->once())
            ->method('getConfig')
            ->willReturn($formConfig);

        $formConfig->expects($this->once())
            ->method('getOption')
            ->with(FormOperationTypeConstant::OPTION_KEY)
            ->willReturn(null);

        $form->expects($this->once())
            ->method('getData')
            ->willReturn('string data');

        $this->requestFlashEventDispatcher
            ->expects($this->never())
            ->method($this->anything());

        $this->simulatePostSubmitEvent($event);
    }

    private function simulatePostSubmitEvent(FormEvent $event): void
    {
        $builder = $this->createMock(FormBuilderInterface::class);
        $builder->expects($this->once())
            ->method('getDataClass')
            ->willReturn(stdClass::class);

        $eventCallback = null;
        $builder->expects($this->once())
            ->method('addEventListener')
            ->willReturnCallback(static function ($eventName, $callback) use (&$eventCallback, $builder) {
                $eventCallback = $callback;

                return $builder;
            });

        $this->validationListenerExtension->buildForm($builder, []);

        if (is_callable($eventCallback)) {
            $eventCallback($event);
        }
    }
}
