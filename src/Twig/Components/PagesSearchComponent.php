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
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent(name: 'pages_search')]
final class PagesSearchComponent extends DatatableComponent
{
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
                'p.created_at' => [
                    'label' => $this->translator->trans('created_at'),
                    'sortable' => true
                ],
                'p.updated_at' => [
                    'label' => $this->translator->trans('updated_at'),
                    'sortable' => true
                ]
            ],
            "quick_filters" => [
                "p.title" => []
            ],
            "filters" => [
                'p.title' => [
                    'label' => 'Name'
                ],
            ]
        ];
    }
}
