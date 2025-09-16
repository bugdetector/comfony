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

use App\Entity\Auth\User;
use App\Entity\Auth\UserStatus;
use Doctrine\ORM\QueryBuilder;
use Override;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveProp;

#[AsLiveComponent(template: '@base_theme/partials/_datatable.html.twig')]
final class UsersSearchComponent extends DatatableComponent
{
    #[LiveProp(writable: true, url: true)]
    public string $sort = 'u.createdAt';
    #[LiveProp(writable: true, url: true)]
    public string $direction = 'DESC';

    #[LiveProp(writable: false)]
    public ?string $rowTemplateFile = "admin/users/_row.html.twig";

    #[Override]
    public function getQueryBuilder(): QueryBuilder
    {
        return $this->entityManager->getRepository(User::class)->createQueryBuilder('u');
    }

    #[Override]
    public function getTableBuildData(): array
    {
        return [
            'headers' => [
                [
                    'label' => $this->translator->trans("Actions")
                ],
                'u.id' => [
                    'label' => $this->translator->trans("Id"),
                    'sortable' => true,
                ],
                'u.name' => [
                    'label' => $this->translator->trans("Name"),
                    'sortable' => true,
                ],
                'u.email' => [
                    'label' => $this->translator->trans("Email"),
                    'sortable' => true,
                ],
                [
                    'label' => $this->translator->trans('Status'),
                ],
                [
                    'label' => $this->translator->trans("Roles")
                ],
                'u.last_access' => [
                    'label' => $this->translator->trans('Last Access'),
                    'sortable' => true
                ],
                'u.createdAt' => [
                    'label' => $this->translator->trans('Registration Date'),
                    'sortable' => true
                ]
            ],
            "quick_filters" => [
                "u.name" => [],
                "u.email" => []
            ],
            "filters" => [
                'u.name' => [
                    'options' => [
                        'label' => 'Name',
                    ]
                ],
                'u.email' => [
                    'options' => [
                        'label' => 'Email',
                    ]
                ],
                'u.roles' => [
                    'type' => ChoiceType::class,
                    'options' => [
                        'label' => 'Roles',
                        'placeholder' => $this->translator->trans('All'),
                        'choices' => User::ROLES,
                    ]
                ],
                'u.status' => [
                    'type' => ChoiceType::class,
                    'options' => [
                        'label' => 'Status',
                        'placeholder' => $this->translator->trans('All'),
                        'choices' => [
                            'Active' => UserStatus::Active->name,
                            'Blocked' => UserStatus::Blocked->name,
                            'Banned' => UserStatus::Banned->name,
                        ]
                    ]
                ],
                'u.createdAtStart' => [
                    'type' => DateType::class,
                    'column' => 'u.createdAt',
                    'comparison' => '>=',
                    'options' => [
                        'label' => 'Registration Date (From)',
                    ]
                ],
                'u.createdAtEnd' => [
                    'type' => DateType::class,
                    'column' => 'u.createdAt',
                    'comparison' => '<=',
                    'valueProcessor' => function ($value) {
                        return $value . " 23:59:59";
                    },
                    'options' => [
                        'label' => 'Registration Date (To)',
                    ]
                ],
            ]
        ];
    }
}
