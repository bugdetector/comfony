<?php

namespace App\Twig\Components;

use App\Entity\Configuration\Configuration;
use Doctrine\ORM\QueryBuilder;
use Override;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveProp;

#[AsLiveComponent(template: '@base_theme/partials/_datatable.html.twig')]
final class ConfigurationsSearchComponent extends DatatableComponent
{
    #[LiveProp(writable: true, url: true)]
    public string $sort = 'c.createdAt';
    #[LiveProp(writable: true, url: true)]
    public string $direction = 'DESC';

    #[LiveProp(writable: false)]
    public ?string $rowTemplateFile = "admin/configuration/_row.html.twig";

    #[Override]
    public function getQueryBuilder(): QueryBuilder
    {
        return $this->entityManager->getRepository(Configuration::class)->createQueryBuilder('c');
    }

    #[Override]
    public function getTableBuildData(): array
    {
        return [
            'headers' => [
                [
                    'label' => $this->translator->trans("Actions")
                ],
                'c.id' => [
                    'label' => $this->translator->trans("Id"),
                    'sortable' => true,
                ],
                'c.configKey' => [
                    'label' => $this->translator->trans("Config Key"),
                    'sortable' => true,
                ],
                'c.value' => [
                    'label' => $this->translator->trans("Value"),
                    'sortable' => false,
                ],
                'c.createdAt' => [
                    'label' => $this->translator->trans('created_at'),
                    'sortable' => true
                ],
                'c.updatedAt' => [
                    'label' => $this->translator->trans('updated_at'),
                    'sortable' => true
                ]
            ],
            "quick_filters" => [
                "c.configKey" => []
            ],
            "filters" => [
                'c.configKey' => [
                    'options' => [
                        'label' => 'Config Key',
                    ],
                ],
            ]
        ];
    }
}
