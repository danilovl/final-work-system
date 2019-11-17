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
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Datagrid\{
    ListMapper,
    DatagridMapper
};

class WorkAdmin extends AbstractAdmin
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
            ->add('title')
            ->add('shortcut')
            ->add('deadline')
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
            ->addIdentifier('title')
            ->add('shortcut')
            ->add('deadline')
            ->add('author')
            ->add('supervisor')
            ->add('opponent')
            ->add('consultant')
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
        $formMapper->add('title')
            ->add('shortcut')
            ->add('deadline')
            ->add('type')
            ->add('status')
            ->add('categories')
            ->add('author')
            ->add('supervisor')
            ->add('opponent')
            ->add('consultant')
            ->add('author');
    }

    /**
     * @param ShowMapper $showMapper
     * @throws RuntimeException
     */
    protected function configureShowFields(ShowMapper $showMapper): void
    {
        $showMapper->add('id')
            ->add('title')
            ->add('shortcut')
            ->add('deadline')
            ->add('createdAt')
            ->add('updatedAt');
    }
}
