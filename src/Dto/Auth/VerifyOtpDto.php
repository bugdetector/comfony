<?php

namespace App\Dto\Auth;

use Symfony\Component\Validator\Constraints as Assert;

final class VerifyOtpDto
{
    #[Assert\Email]
    public $email;

    #[Assert\NotBlank()]
    public $code;
}
