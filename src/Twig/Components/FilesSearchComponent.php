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
use App\Repository\FileRepository;
use Doctrine\ORM\QueryBuilder;
use Knp\Component\Pager\PaginatorInterface;
use Override;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent(name: 'files_search')]
final class FilesSearchComponent extends DatatableComponent
{
    use DefaultActionTrait;

    public function __construct(
        protected readonly PaginatorInterface $paginator,
        protected readonly TranslatorInterface $translator,
        private readonly FileRepository $fileRepository
    ) {
    }

    #[Override]
    public function getQueryBuilder(): QueryBuilder
    {
        return $this->fileRepository->createQueryBuilder('f');
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
                    'label' => $this->translator->trans("File Name"),
                    'sortable' => true,
                ],
                'f.file_size' => [
                    'label' => $this->translator->trans("Size"),
                    'sortable' => true,
                ],
                [
                    'label' => $this->translator->trans('Mime Type'),
                ],
                [
                    'label' => $this->translator->trans("Extension")
                ],
                'f.created_at' => [
                    'label' => $this->translator->trans('Created At'),
                    'sortable' => true
                ],
                'f.updated_at' => [
                    'label' => $this->translator->trans('Updated At'),
                    'sortable' => true
                ]
            ],
            "filters" => [
                'f.file_name' => [
                    'label' => 'Name'
                ],
            ]
        ];
    }
}
