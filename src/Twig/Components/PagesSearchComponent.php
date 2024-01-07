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
use App\Repository\Page\PageRepository;
use Doctrine\ORM\QueryBuilder;
use Knp\Component\Pager\PaginatorInterface;
use Override;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent(name: 'pages_search')]
final class PagesSearchComponent extends DatatableComponent
{
    use DefaultActionTrait;

    public function __construct(
        protected readonly PaginatorInterface $paginator,
        protected readonly TranslatorInterface $translator,
        private readonly PageRepository $pageRepository
    ) {
    }

    #[Override]
    public function getQueryBuilder(): QueryBuilder
    {
        return $this->pageRepository->createQueryBuilder('p');
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
                'p.name' => [
                    'label' => $this->translator->trans("Title"),
                    'sortable' => true,
                ],
                [
                    'label' => $this->translator->trans('Published'),
                ],
                'u.created_at' => [
                    'label' => $this->translator->trans('created_at'),
                    'sortable' => true
                ],
                'u.updated_at' => [
                    'label' => $this->translator->trans('created_at'),
                    'sortable' => true
                ]
            ],
            "filters" => [
                'p.title' => [
                    'label' => 'Name'
                ],
            ]
        ];
    }
}
