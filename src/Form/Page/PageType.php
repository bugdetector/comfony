<?php

namespace App\Form\Page;

use App\Entity\Page\Page;
use App\Form\Type\AsyncFileType;
use App\Repository\Page\PageRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\String\Slugger\SluggerInterface;

class PageType extends AbstractType
{
    public function __construct(
        private SluggerInterface $slugger,
        private PageRepository $pageRepository
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title')
            ->add('body', options: [
                'attr' => [
                    'data-controller' => 'tinymce'
                ]
            ])
            ->add('attachments', AsyncFileType::class, [
                'multiple' => true
            ])
            ->add('published', options: [
                'required' => false
            ])
            ->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) {
                /** @var Page $page */
                $page = $event->getData();
                if (null === $page->getSlug() && null !== $page->getTitle()) {
                    $slug = $this->slugger->slug($page->getTitle())->lower();
                    $tempCount = 1;
                    $tempSlug = $slug;
                    while ($this->pageRepository->findBy(["slug" => $tempSlug])) {
                        $tempSlug = $slug . '-' . $tempCount;
                        $tempCount++;
                    }
                    $page->setSlug($tempSlug);
                }
            })
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Page::class,
        ]);
    }
}
