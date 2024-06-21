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
use Doctrine\ORM\QueryBuilder;
use Override;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;

#[AsLiveComponent(name: 'users_search')]
final class UsersSearchComponent extends DatatableComponent
{
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
                    'label' => 'Name'
                ],
                'u.email' => [
                    'label' => 'Email'
                ]
            ]
        ];
    }
}
