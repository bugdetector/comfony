<?php

namespace App\Entity\Page;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Translatable\Entity\MappedSuperclass\AbstractPersonalTranslation;

#[ORM\Entity]
#[ORM\Table(name: 'page_translations')]
class PageTranslation extends AbstractPersonalTranslation
{
    #[ORM\ManyToOne(targetEntity: Page::class, inversedBy: "translations")]
    #[ORM\JoinColumn(name: "object_id", referencedColumnName: "id", onDelete: "CASCADE")]
    protected $object;
}
