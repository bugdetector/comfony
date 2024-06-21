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
use Doctrine\ORM\QueryBuilder;
use Override;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;

#[AsLiveComponent(name: 'files_search')]
final class FilesSearchComponent extends DatatableComponent
{
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
                    'label' => 'Name'
                ],
            ]
        ];
    }
}
