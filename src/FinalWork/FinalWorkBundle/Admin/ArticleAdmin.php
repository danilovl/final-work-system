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

class ArticleAdmin extends AbstractAdmin
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
            ->add('content')
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
            ->add('owner')
            ->add('categories')
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
     */
    protected function configureFormFields(FormMapper $formMapper): void
    {
        $formMapper->add('title')
            ->add('content', 'ckeditor', [
                'required' => true,
                'attr' => [
                    'rows' => 6,
                ],
            ])
            ->add('active')
            ->add('owner')
            ->add('categories');
    }

    /**
     * @param ShowMapper $showMapper
     * @throws RuntimeException
     */
    protected function configureShowFields(ShowMapper $showMapper): void
    {
        $showMapper->add('id')
            ->add('title')
            ->add('content')
            ->add('active')
            ->add('createdAt')
            ->add('updatedAt');
    }
}
