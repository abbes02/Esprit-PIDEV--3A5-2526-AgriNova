<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Entite fille JOINED : table produit_agricole.
 */
#[ORM\Entity]
#[ORM\Table(name: 'produit_agricole')]
class ProduitAgricole extends Produit
{
    #[ORM\Column(name: 'CategorieAgricole', type: 'string', length: 100, nullable: true)]
    private ?string $categorieAgricole = null;

    #[ORM\Column(name: 'DateExpiration', type: 'date', nullable: true)]
    #[Assert\Date]
    private ?\DateTimeInterface $dateExpiration = null;

    public function getCategorieAgricole(): ?string
    {
        return $this->categorieAgricole;
    }

    public function setCategorieAgricole(?string $categorieAgricole): self
    {
        $this->categorieAgricole = $categorieAgricole;

        return $this;
    }

    public function getDateExpiration(): ?\DateTimeInterface
    {
        return $this->dateExpiration;
    }

    public function setDateExpiration(?\DateTimeInterface $dateExpiration): self
    {
        $this->dateExpiration = $dateExpiration;

        return $this;
    }
}
