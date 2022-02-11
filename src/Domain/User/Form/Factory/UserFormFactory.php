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

namespace App\Domain\User\Form\Factory;

use App\Application\Constant\ControllerMethodConstant;
use App\Application\Exception\RuntimeException;
use App\Domain\User\Form\{
    UserForm,
    UserEditForm
};
use App\Domain\User\Entity\User;
use App\Domain\User\UserModel;
use Danilovl\HashidsBundle\Interfaces\HashidsServiceInterface;
use Symfony\Component\Form\{
    FormFactoryInterface,
    FormInterface
};
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;

class UserFormFactory
{
    public function __construct(
        private RouterInterface $router,
        private HashidsServiceInterface $hashidsService,
        private FormFactoryInterface $formFactory
    ) {
    }

    public function getUserForm(
        string $type,
        UserModel $userModel,
        User $user = null
    ): FormInterface {
        $parameters = [];

        $formClass = UserForm::class;
        switch ($type) {
            case ControllerMethodConstant::EDIT:
                $formClass = UserEditForm::class;
                break;
            case ControllerMethodConstant::CREATE:
                break;
            case ControllerMethodConstant::CREATE_AJAX:
                $parameters = [
                    'action' => $this->router->generate('user_create_ajax'),
                    'method' => Request::METHOD_POST
                ];
                break;
            case ControllerMethodConstant::EDIT_AJAX:
                $formClass = UserEditForm::class;
                $parameters = [
                    'action' => $this->router->generate('user_edit_ajax', [
                        'id' => $this->hashidsService->encode($user->getId())
                    ]),
                    'method' => Request::METHOD_POST,
                ];
                break;
            default:
                throw new RuntimeException('Controller method type not found');
        }

        return $this->formFactory->create($formClass, $userModel, $parameters);
    }
}
