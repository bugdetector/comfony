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

use App\Repository\Auth\UserRepository;
use Doctrine\ORM\QueryBuilder;
use Knp\Component\Pager\PaginatorInterface;
use Override;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent(name: 'users_search')]
final class UsersSearchComponent extends DatatableComponent
{
    use DefaultActionTrait;

    public function __construct(
        protected readonly PaginatorInterface $paginator,
        protected readonly TranslatorInterface $translator,
        private readonly UserRepository $userRepository
    ) {
    }

    #[Override]
    public function getQueryBuilder(): QueryBuilder
    {
        return $this->userRepository->createQueryBuilder('u');
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
                'u.created_at' => [
                    'label' => $this->translator->trans('Registration Date'),
                    'sortable' => true
                ]
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
