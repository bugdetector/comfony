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
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent(name: 'users_search')]
final class UsersSearchComponent
{
    use DefaultActionTrait;

    #[LiveProp(writable: true)]
    public string $query = '';

    #[LiveProp(writable: true)]
    public int $page = 1;

    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly PaginatorInterface $paginator
    ) {
    }

    /**
     * @return PaginationInterface
     */
    public function getPagination(): PaginationInterface
    {
        $queryBuilder = $this->userRepository->createQueryBuilder('u');

        $queryBuilder
                ->orWhere('u.name LIKE :query')
                ->orWhere('u.email LIKE :query')
                ->setParameter('query', '%' . $this->query . '%')
                ->orderBy('u.id', 'ASC');


        return $this->paginator->paginate(
            $queryBuilder,
            $this->page,
            1
        );
    }
}
