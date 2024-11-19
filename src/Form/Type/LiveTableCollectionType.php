<?php

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\UX\LiveComponent\Form\Type\LiveCollectionType;

class LiveTableCollectionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $resizePrototypeOptions = array_replace($options['entry_options'], $options['prototype_options']);
        $prototypeOptions = array_replace([
            'required' => $options['required'],
            'label' => $options['prototype_name'] . 'label__',
        ], $resizePrototypeOptions);

        if (null !== $options['prototype_data']) {
            $prototypeOptions['data'] = $options['prototype_data'];
        }

        $prototype = $builder->create($options['prototype_name'], $options['entry_type'], $prototypeOptions);
        $builder->setAttribute('prototype', $prototype->getForm());
    }

    public function getParent()
    {
        return LiveCollectionType::class;
    }
}
