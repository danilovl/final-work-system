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

namespace App\Model\UserGroup\Form\Factory;

use App\Constant\ControllerMethodConstant;
use App\Entity\Group;
use App\Exception\RuntimeException;
use App\Model\UserGroup\Form\UserGroupForm;
use App\Model\UserGroup\UserGroupModel;
use Danilovl\HashidsBundle\Interfaces\HashidsServiceInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Form\{
    FormInterface,
    FormFactoryInterface
};
use Symfony\Component\HttpFoundation\Request;

class UserGroupFormFactory
{
    public function __construct(
        private RouterInterface $router,
        private HashidsServiceInterface $hashidsService,
        private FormFactoryInterface $formFactory
    ) {
    }

    public function getUserGroupForm(
        string $type,
        UserGroupModel $userGroupModel,
        Group $group = null
    ): FormInterface {
        $parameters = [];

        switch ($type) {
            case ControllerMethodConstant::EDIT:
            case ControllerMethodConstant::CREATE:
                break;
            case ControllerMethodConstant::CREATE_AJAX:
                $parameters = [
                    'action' => $this->router->generate('user_group_create_ajax'),
                    'method' => Request::METHOD_POST
                ];

                break;
            case ControllerMethodConstant::EDIT_AJAX:
                $parameters = [
                    'action' => $this->router->generate('user_group_edit_ajax', [
                        'id' => $this->hashidsService->encode($group->getId())
                    ]),
                    'method' => Request::METHOD_POST
                ];

                break;
            default:
                throw new RuntimeException('Controller method type not found');
        }

        return $this->formFactory->create(UserGroupForm::class, $userGroupModel, $parameters);
    }
}
