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
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\Attribute\LiveListener;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\Attribute\PreReRender;
use Symfony\UX\LiveComponent\ComponentToolsTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Symfony\UX\TwigComponent\Attribute\PostMount;

#[AsLiveComponent(template: '@base_theme/partials/_datatable.html.twig')]
abstract class DatatableComponent extends AbstractController
{
    use DefaultActionTrait;
    use ComponentToolsTrait;

    public QueryBuilder $queryBuilder;

    #[LiveProp(writable: true, url: true, onUpdated: 'onQueryUpdated')]
    public string $query = '';
    #[LiveProp(writable: true, url: true)]
    public string $sort = '';
    #[LiveProp(writable: true, url: true)]
    public string $direction = '';
    #[LiveProp(writable: true, url: true)]
    public int $page = 1;
    #[LiveProp(writable: true, url: true)]
    public array $filters = [];
    #[LiveProp(writable: true, url: true)]
    public int $pageSize = 25;
    #[LiveProp(writable: false)]
    public ?string $listTopic = null;

    #[LiveProp(writable: false)]
    public ?string $rowTemplateFile = null;

    public array $headers = [];

    public $filterForm = null;

    public function __construct(
        protected readonly PaginatorInterface $paginator,
        protected readonly TranslatorInterface $translator,
        protected readonly EntityManagerInterface $entityManager,
        protected readonly FormFactoryInterface $formFactory,
    ) {
    }

    /**
     * Get query builder to paginate.
     * @return QueryBuilder
     */
    abstract public function getQueryBuilder(): QueryBuilder;

    /**
     * Get table build data.
     * @return array
     */
    abstract public function getTableBuildData(): array;

    #[PostMount()]
    #[PreReRender()]
    public function initializeDatatable()
    {
        $this->dispatchBrowserEvent('live-component:update');
        $buildData = $this->getTableBuildData();
        if (@$buildData['filters']) {
            $builder = $this->formFactory->createNamedBuilder('filters', options: [
                'method' => 'GET',
                'csrf_protection' => false,
            ]);
            foreach ($buildData['filters'] as $name => $filter) {
                $inputName = str_replace('.', '-', $name);
                $options = @$filter['options'] ?: [];
                $options['required'] = false;
                $options['attr']['data-model'] = "debounce(300)|filters[$inputName]";
                $options['attr']['autocomplete'] = "off";
                $builder->add($inputName, @$filter['type'], $options);
                $this->filters[$inputName] = $this->filters[$inputName] ?? null;
            }
            $this->filterForm = $builder->getForm()->createView();
        }
    }

    /**
     * @return PaginationInterface
     */
    public function getPagination(): PaginationInterface
    {
        $this->queryBuilder = $this->getQueryBuilder();
        $buildData = $this->getTableBuildData();
        if ($this->query) {
            $conditions = [];
            foreach ($buildData["quick_filters"] as $column => $data) {
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
        if ($this->filters) {
            $buildFilters = @$this->getTableBuildData()['filters'];
            foreach ($this->filters as $inputName => $value) {
                if ($value === null || $value === '') {
                    continue;
                }
                $name = str_replace('-', '.', $inputName);
                $column = @$buildFilters[$name]['column'] ?? $name;
                $comparison = @$buildFilters[$name]['comparison'] ?: 'LIKE';
                $placeholder = 'filter_' . preg_replace('/[^A-Za-z]/', '_', $name);
                $this->queryBuilder->andWhere(
                    "{$column} {$comparison} :{$placeholder}"
                );
                if (@$buildFilters[$name]['valueProcessor']) {
                    $value = $buildFilters[$name]['valueProcessor']($value);
                }
                if (strcasecmp('LIKE', $comparison) === 0) {
                    $this->queryBuilder->setParameter($placeholder, '%' . $value . '%');
                } else {
                    $this->queryBuilder->setParameter($placeholder, $value);
                }
            }
        }

        return $this->paginator->paginate(
            $this->queryBuilder,
            $this->page,
            $this->pageSize,
            [
                "defaultSortFieldName" => $this->sort,
                "defaultSortDirection" => $this->direction
            ]
        );
    }

    #[LiveAction]
    public function setSort(#[LiveArg] $sort, #[LiveArg] $direction)
    {
        $this->sort = $sort;
        $this->direction = $direction;
    }

    public function onQueryUpdated($previousValue): void
    {
        $this->emit('queryUpdated');
    }

    #[LiveListener('queryUpdated')]
    public function setPageStart()
    {
        $this->page = 1;
    }

    #[LiveAction]
    public function resetFilters()
    {
        $this->dispatchBrowserEvent('live-component:update');
        $this->filters = [];
    }
}
