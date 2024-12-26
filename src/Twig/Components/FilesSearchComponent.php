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

use App\Entity\File\File;
use App\Entity\File\FileStatus;
use Doctrine\ORM\QueryBuilder;
use Override;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveProp;

#[AsLiveComponent()]
final class FilesSearchComponent extends DatatableComponent
{
    #[LiveProp(writable: true, url: true)]
    public string $sort = 'f.createdAt';
    #[LiveProp(writable: true, url: true)]
    public string $direction = 'DESC';

    #[Override]
    public function getQueryBuilder(): QueryBuilder
    {
        return $this->entityManager->getRepository(File::class)->createQueryBuilder('f');
    }

    #[Override]
    public function getTableBuildData(): array
    {
        return [
            'headers' => [
                [
                    'label' => $this->translator->trans("Actions")
                ],
                'f.id' => [
                    'label' => $this->translator->trans("Id"),
                    'sortable' => true,
                ],
                'f.file_name' => [
                    'label' => $this->translator->trans("file_name"),
                    'sortable' => true,
                ],
                'f.file_size' => [
                    'label' => $this->translator->trans("file_size"),
                    'sortable' => true,
                ],
                [
                    'label' => $this->translator->trans('mime_type'),
                ],
                [
                    'label' => $this->translator->trans("Extension")
                ],
                [
                    'label' => $this->translator->trans("Status"),
                ],
                'f.createdAt' => [
                    'label' => $this->translator->trans('created_at'),
                    'sortable' => true
                ],
                'f.updatedAt' => [
                    'label' => $this->translator->trans('updated_at'),
                    'sortable' => true
                ]
            ],
            "quick_filters" => [
                "f.file_name" => []
            ],
            "filters" => [
                'f.file_name' => [
                    'options' => [
                        'label' => 'file_name',
                    ]
                ],
                'f.mime_type' => [
                    'options' => [
                        'label' => 'mime_type',
                    ]
                ],
                'f.extension' => [
                    'options' => [
                        'label' => 'Extension',
                    ]
                ],
                'f.status' => [
                    'type' => ChoiceType::class,
                    'options' => [
                        'label' => 'Status',
                        'placeholder' => $this->translator->trans('All'),
                        'choices' => [
                            FileStatus::Permanent->value => FileStatus::Permanent->name,
                            FileStatus::Temporary->value => FileStatus::Temporary->name,
                            FileStatus::Deleted->value => FileStatus::Deleted->name,
                        ],
                    ]
                ],
                'f.createdAtStart' => [
                    'type' => DateType::class,
                    'column' => 'f.createdAt',
                    'comparison' => '>=',
                    'options' => [
                        'label' => 'Created At (From)',
                    ]
                ],
                'f.createdAtEnd' => [
                    'type' => DateType::class,
                    'column' => 'f.createdAt',
                    'comparison' => '<=',
                    'valueProcessor' => function ($value) {
                        return $value . " 23:59:59";
                    },
                    'options' => [
                        'label' => 'Created At (To)',
                    ]
                ],
            ]
        ];
    }
}
