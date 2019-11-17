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

class ArticleCategoryAdmin extends AbstractAdmin
{
    /**
     * @var array
     */
    protected $datagridValues = [
        '_page' => 1,
        '_sort_order' => 'DESC',
        '_sort_by' => 'createdAt',
    ];

    /**
     * @param DatagridMapper $datagridMapper
     * @throws RuntimeException
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper): void
    {
        $datagridMapper->add('id')
            ->add('name')
            ->add('description')
            ->add('createdAt')
            ->add('updatedAt');
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
            ->add('owner')
            ->add('active')
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
     * @throws RuntimeException
     */
    protected function configureFormFields(FormMapper $formMapper): void
    {
        $formMapper->tab('General')
            ->add('name')
            ->add('description')
            ->add('owner')
            ->add('active')
            ->end()
            ->end()
            ->tab('Security')
            ->add('access', 'sonata_security_roles', [
                'label' => 'form.label_roles',
                'expanded' => true,
                'multiple' => true,
                'required' => false
            ])
            ->end();
    }

    /**
     * @param ShowMapper $showMapper
     * @throws RuntimeException
     */
    protected function configureShowFields(ShowMapper $showMapper): void
    {
        $showMapper->add('id')
            ->add('name')
            ->add('active')
            ->add('description')
            ->add('createdAt')
            ->add('updatedAt');
    }
}
