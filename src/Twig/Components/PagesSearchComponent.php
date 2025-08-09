<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Twig\Components;

use App\Entity\Page\Page;
use Doctrine\ORM\QueryBuilder;
use Override;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveProp;

#[AsLiveComponent(template: '@base_theme/partials/_datatable.html.twig')]
final class PagesSearchComponent extends DatatableComponent
{
    #[LiveProp(writable: true, url: true)]
    public string $sort = 'p.createdAt';
    #[LiveProp(writable: true, url: true)]
    public string $direction = 'DESC';

    #[LiveProp(writable: false)]
    public ?string $rowTemplateFile = "admin/page/_row.html.twig";

    #[Override]
    public function getQueryBuilder(): QueryBuilder
    {
        return $this->entityManager->getRepository(Page::class)->createQueryBuilder('p');
    }

    #[Override]
    public function getTableBuildData(): array
    {
        return [
            'headers' => [
                [
                    'label' => $this->translator->trans("Actions")
                ],
                'p.id' => [
                    'label' => $this->translator->trans("Id"),
                    'sortable' => true,
                ],
                'p.title' => [
                    'label' => $this->translator->trans("Title"),
                    'sortable' => true,
                ],
                [
                    'label' => $this->translator->trans('Published'),
                ],
                'p.createdAt' => [
                    'label' => $this->translator->trans('created_at'),
                    'sortable' => true
                ],
                'p.updatedAt' => [
                    'label' => $this->translator->trans('updated_at'),
                    'sortable' => true
                ]
            ],
            "quick_filters" => [
                "p.title" => []
            ],
            "filters" => [
                'p.title' => [
                    'options' => [
                        'label' => 'Title',
                    ],
                ],
                'p.published' => [
                    'type' => ChoiceType::class,
                    'options' => [
                        'label' => 'Published',
                        'placeholder' => $this->translator->trans('All'),
                        'choices' => [
                            'Published' => 1,
                            'Unpublished' => 0,
                        ],
                    ],
                ],
            ]
        ];
    }
}
