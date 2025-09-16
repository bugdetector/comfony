<?php

namespace App\Form\Page;

use App\Entity\Category\Category;
use App\Entity\Page\Page;
use App\Form\Type\LiveAsyncFileType;
use App\Repository\Page\PageRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PageFormType extends AbstractType
{
    public function __construct(
        private PageRepository $pageRepository
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title')
            ->add('category', EntityType::class, [
                'class' => Category::class,
                'choice_label' => 'name',
            ])
            ->add('body', options: [
                'attr' => [
                    'data-controller' => 'tinymce'
                ]
            ])
            ->add('attachments', LiveAsyncFileType::class, [
                'multiple' => true
            ])
            ->add('published', options: [
                'required' => false
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Page::class,
        ]);
    }
}
