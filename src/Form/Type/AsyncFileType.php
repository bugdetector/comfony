<?php

namespace App\Form\Type;

use App\Entity\File\File;
use App\Form\Extension\DataTransformer\AsyncFileTransformer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AsyncFileType extends AbstractType
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private AsyncFileTransformer $asyncFileTransformer,
    ) {
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $dataClass = null;
        if (class_exists(File::class)) {
            $dataClass = static fn (Options $options) => $options['multiple'] ? null : File::class;
        }

        $emptyData = static fn (Options $options) => $options['multiple'] ? [] : null;

        $resolver->setDefaults([
            'compound' => false,
            'data_class' => $dataClass,
            'empty_data' => $emptyData,
            'multiple' => false,
            'allow_file_upload' => true,
            'invalid_message' => 'Please select a valid file.',
            'allow_delete' => true,
        ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addViewTransformer($this->asyncFileTransformer);
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        if ($options['multiple']) {
            $view->vars['full_name'] .= '[]';
            $view->vars['attr']['multiple'] = 'multiple';
        }
        $view->vars['allow_delete'] = $options['allow_delete'];

        $view->vars = array_replace($view->vars, [
            'type' => 'hidden',
            'value' => $view->vars['value'] instanceof File ? $view->vars['value']->getId() : null
        ]);
    }
}
