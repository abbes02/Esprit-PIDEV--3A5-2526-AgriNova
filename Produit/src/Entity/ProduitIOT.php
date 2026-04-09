<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Entite fille JOINED : table produit_iot.
 */
#[ORM\Entity]
#[ORM\Table(name: 'produit_iot')]
class ProduitIOT extends Produit
{
    public const CATEGORIES = [
        'Capteur Temprature',
        'Capteur Humidit Air',
    ];

    #[ORM\Column(name: 'Categorie', type: 'string', length: 255, nullable: true)]
    #[Assert\Choice(choices: self::CATEGORIES)]
    private ?string $categorie = null;

    public function getCategorie(): ?string
    {
        return $this->categorie;
    }

    public function setCategorie(?string $categorie): self
    {
        $this->categorie = $categorie;

        return $this;
    }
}
