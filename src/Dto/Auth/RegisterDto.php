<?php

namespace App\Dto\Auth;

use Symfony\Component\Validator\Constraints as Assert;

final class RegisterDto
{
    #[Assert\NotBlank()]
    public ?string $register_token = null;

    #[Assert\NotBlank]
    #[Assert\Length(max: 75)]
    public ?string $name = null;

    #[Assert\NotBlank]
    #[Assert\Length(max: 75)]
    public ?string $surname = null;

    #[Assert\NotBlank]
    #[Assert\Length(max: 75)]
    public ?string $companyName = null;

    #[Assert\NotBlank]
    #[Assert\Length(min: 6, minMessage: "Your password should be at least {{ limit }} characters", max: 100)]
    public ?string $password;
}
