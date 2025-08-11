<?php

namespace App\Entity\Auth;

use App\Repository\Auth\ForgetPasswordTokenRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ForgetPasswordTokenRepository::class)]
class ForgetPasswordToken
{
    use TimestampableEntity;

    public const COUNTDOWN = 300;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(cascade: ['persist'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\Column]
    #[Assert\GreaterThanOrEqual(100000)]
    #[Assert\LessThanOrEqual(999999)]
    private ?int $code = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $reset_token = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getCode(): ?int
    {
        return $this->code;
    }

    public function setCode(int $code): static
    {
        $this->code = $code;

        return $this;
    }

    public function isValid()
    {
        return $this->getUpdatedAt()->getTimestamp() >= strtotime("-" . self::COUNTDOWN . " seconds");
    }

    public function getResetToken(): ?string
    {
        return $this->reset_token;
    }

    public function setResetToken(?string $reset_token): static
    {
        $this->reset_token = $reset_token;

        return $this;
    }
}
