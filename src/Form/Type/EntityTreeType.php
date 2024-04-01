<?php

namespace App\Form\Type;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\ChoiceList\View\ChoiceView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EntityTreeType extends AbstractType
{
    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'parent_method_name' => 'getParent',
            'children_method_name' => 'getChildren',
            'name_method_name' => 'getName',
            'prefix' => '--',
        ]);
    }

    /**
     * @param FormView      $view
     * @param FormInterface $form
     * @param array         $options
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $choices = [];

        $parent_method_name = $options['parent_method_name'];
        foreach ($view->vars['choices'] as $choice) {
            if ($choice->data->$parent_method_name() === null) {
                $choices[$choice->value] = $choice->data;
            }
        }

        $choices = $this->buildTreeChoices($choices, $options);

        $view->vars['choices'] = $choices;
    }

    /**
     * @param object[] $choices
     * @param array    $options
     * @param int      $level
     *
     * @return array
     */
    protected function buildTreeChoices($choices, array $options, $level = 0)
    {

        $result = [];
        $children_method_name = $options['children_method_name'];
        $name_method_name = $options['name_method_name'];


        foreach ($choices as $choice) {
            $result[$choice->getId()] = new ChoiceView(
                $choice,
                (string)$choice->getId(),
                str_repeat($options['prefix'], $level) . ' ' . $choice->$name_method_name(),
                []
            );

            if (!$choice->$children_method_name()->isEmpty()) {
                $result = array_merge(
                    $result,
                    $this->buildTreeChoices($choice->$children_method_name(), $options, $level + 1)
                );
            }
        }

        return $result;
    }

    /**
     * @return string|null
     */
    public function getParent()
    {
        return EntityType::class;
    }
}
