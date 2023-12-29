<?php

namespace App\Entity\Traits;

use DateTime;
use DateTimeInterface;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation\Timestampable;
use Exception;

/**
 * Trait TimeStampableTrait
 * @package App\Entity\Trait
 */
trait TimestampableTrait
{
    #[ORM\Column(type: Types::DATETIME_MUTABLE, options:["default" => "CURRENT_TIMESTAMP"])]
    #[Timestampable(on:'create')]
    private ?\DateTimeInterface $created_at = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, options:["default" => "CURRENT_TIMESTAMP"])]
    #[Timestampable(on:'update')]
    private ?\DateTimeInterface $updated_at = null;

    /**
     * @return DateTimeInterface|null
     * @throws Exception
     */
    public function getCreatedAt(): ?DateTimeInterface
    {
        return $this->createdAt ?? new DateTime();
    }

    /**
     * @param DateTimeInterface $createdAt
     * @return $this
     */
    public function setCreatedAt(DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @return DateTimeInterface|null
     */
    public function getUpdatedAt(): ?DateTimeInterface
    {
        return $this->updatedAt ?? new DateTime();
    }

    /**
     * @param DateTimeInterface $updatedAt
     * @return $this
     */
    public function setUpdatedAt(DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    // /**
    //  * @ORM\PrePersist()
    //  * @ORM\PreUpdate()
    //  */
    // public function updateTimestamps(): void
    // {
    //     $now = new DateTime();
    //     $this->setUpdatedAt($now);
    //     if ($this->getId() === null) {
    //         $this->setCreatedAt($now);
    //     }
    // }
}
