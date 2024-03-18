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

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent(template: '@base_theme/partials/datatable.html.twig')]
abstract class DatatableComponent
{
    use DefaultActionTrait;

    public QueryBuilder $queryBuilder;

    public array $headers = [];
    public string $query = '';
    public string $sort = "";
    public string $direction = "";
    public int $page = 1;
    public int $pageSize = 10;
    public ?string $listTopic = null;

    public function __construct(
        protected readonly PaginatorInterface $paginator,
        protected readonly TranslatorInterface $translator,
        protected readonly EntityManagerInterface $entityManager
    ) {
    }

    /**
     * Get query builder to paginate.
     * @return array
     */
    abstract public function getQueryBuilder(): QueryBuilder;

    /**
     * Get table build data.
     * @return array
     */
    abstract public function getTableBuildData(): array;

    /**
     * @return PaginationInterface
     */
    public function getPagination(): PaginationInterface
    {
        $this->queryBuilder = $this->getQueryBuilder();
        if ($this->query) {
            $conditions = [];
            foreach ($this->getTableBuildData()["quick_filters"] as $column => $data) {
                $comparison = @$data['comparison'] ?: 'LIKE';
                $placeholder = 'query_' . preg_replace('/[^A-Za-z]/', '_', $column);
                $conditions[] = "{$column} {$comparison} :{$placeholder}";
                if (strcasecmp('LIKE', $comparison) === 0) {
                    $this->queryBuilder->setParameter($placeholder, '%' . $this->query . '%');
                } else {
                    $this->queryBuilder->setParameter($placeholder, $this->query);
                }
            }
            $this->queryBuilder->andWhere(
                $this->queryBuilder->expr()->orX(
                    ...$conditions
                )
            );
        }

        return $this->paginator->paginate(
            $this->queryBuilder,
            $this->page,
            $this->pageSize,
            [
                "sort" => $this->sort,
                "direction" => $this->direction
            ]
        );
    }
}
