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

use Doctrine\ORM\QueryBuilder;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent(template: '@base_theme/partials/datatable.html.twig')]
abstract class DatatableComponent
{
    use DefaultActionTrait;

    protected readonly PaginatorInterface $paginator;
    protected readonly TranslatorInterface $translator;
    public QueryBuilder $queryBuilder;
    public array $headers = [];

    #[LiveProp(writable: true)]
    public int $pageSize = 10;

    #[LiveProp(writable: true)]
    public string $query = '';

    #[LiveProp(writable: true)]
    public int $sort = 1;

    #[LiveProp(writable: true)]
    public int $direction = 1;

    #[LiveProp(writable: true)]
    public int $page = 1;

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
            foreach ($this->getTableBuildData()["filters"] as $column => $data) {
                $comparison = @$data['comparison'] ?: 'LIKE';
                $placeholder = 'query_' . preg_replace('/[^A-Za-z]/', '_', $column);
                $this->queryBuilder->orWhere("{$column} {$comparison} :{$placeholder}");
                if (strcasecmp('LIKE', $comparison) === 0) {
                    $this->queryBuilder->setParameter($placeholder, '%' . $this->query . '%');
                } else {
                    $this->queryBuilder->setParameter($placeholder, $this->query);
                }
            }
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
