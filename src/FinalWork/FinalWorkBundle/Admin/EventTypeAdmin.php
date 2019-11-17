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

namespace FinalWork\FinalWorkBundle\Admin;

use RuntimeException;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\{
    ListMapper,
    DatagridMapper
};
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;

class EventTypeAdmin extends AbstractAdmin
{
    /**
     * @param DatagridMapper $datagridMapper
     * @throws RuntimeException
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper): void
    {
        $datagridMapper->add('name')
            ->add('color')
            ->add('description')
            ->add('constant');
    }

    /**
     * @param ListMapper $listMapper
     * @throws RuntimeException
     */
    protected function configureListFields(ListMapper $listMapper): void
    {
        $listMapper->addIdentifier('id')
            ->addIdentifier('name')
            ->add('description')
            ->add('color')
            ->add('registrable')
            ->add('constant')
            ->add('createdAt')
            ->add('updatedAt')
            ->add('_action', null, [
                'actions' => [
                    'show' => [],
                    'edit' => [],
                    'delete' => [],
                ],
            ]);
    }

    /**
     * @param FormMapper $formMapper
     */
    protected function configureFormFields(FormMapper $formMapper): void
    {
        $formMapper->add('name')
            ->add('description')
            ->add('color')
            ->add('registrable')
            ->add('constant');
    }

    /**
     * @param ShowMapper $showMapper
     * @throws RuntimeException
     */
    protected function configureShowFields(ShowMapper $showMapper): void
    {
        $showMapper->add('id')
            ->add('name')
            ->add('description')
            ->add('color')
            ->add('registrable')
            ->add('constant')
            ->add('createdAt')
            ->add('updatedAt');
    }
}
