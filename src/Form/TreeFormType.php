<?php

namespace App\Form;

use Arkounay\Bundle\UxCollectionBundle\Form\UxCollectionType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Entity\Trait\TreeEntity;

class TreeFormType extends AbstractType
{
    private const DEFAULT_OPTIONS = [
        'entry_type' => TextType::class,
        'add_label' => 'Add New Item',
        'insert_label' => 'Insert New Item Here',
        'allow_add' => true,
        'allow_delete' => true,
        'allow_drag_and_drop' => true,
        'drag_and_drop_filter' => 'input,textarea,a,button,label',
        'drag_and_drop_prevent_on_filter' => false,
        'display_sort_buttons' => true,
        'display_insert_button' => true,
        'entry_class' => 'card',
        'entry_element_class' => 'collection-content mx-3 mb-3',
        'add_wrapper_class' => 'mb-3',
        'add_class' => 'btn btn-primary',
        'insert_class' => 'btn btn-primary',
        'icon_up' => null,
        'icon_down' => null,
        'position_selector' => null,
        'min' => 0,
        'max' => null,
        'auto_initialize' => false
    ];

    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $viewOptions = array_filter($options, function ($key) {
            return array_key_exists($key, self::DEFAULT_OPTIONS);
        }, ARRAY_FILTER_USE_KEY);
        $builder->add('items', UxCollectionType::class, $viewOptions);
        $builder->addEventListener(
            FormEvents::SUBMIT,
            function (FormEvent $event) {
                $initialData = $event->getForm()->getData()['items'];
                $data = $event->getData();
                /** @var TreeEntity $item */
                foreach (@$data["items"] ?: [] as $item) {
                    foreach ($item->getChildren() as $child) {
                        $child->setParent($item);
                    }
                    $this->entityManager->persist($item);
                    array_shift($initialData);
                }
                foreach ($initialData as $initial) {
                    $this->entityManager->remove($initial);
                }
                $this->entityManager->flush();
            }
        );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(self::DEFAULT_OPTIONS);
    }
}
