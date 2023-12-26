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
use App\Repository\Auth\UserRepository;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent(name: 'users_search')]
final class UsersSearchComponent
{
    use DefaultActionTrait;

    #[LiveProp(writable: true)]
    public string $query = '';

    public function __construct(
        private readonly UserRepository $userRepository,
    ) {
    }

    /**
     * @return array<User>
     */
    public function getUsers(): array
    {
        return $this->userRepository->findBySearchQuery($this->query);
    }
}
