<?php

namespace App\Entity;

use App\Repository\ParcelleRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ParcelleRepository::class)]
class Parcelle
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'IdParcelle')]
    private ?int $idParcelle = null;

    #[ORM\Column(name: 'Proprietaire', length: 100)]
    private ?string $proprietaire = null;

    #[ORM\ManyToOne(targetEntity: Utilisateur::class)]
    #[ORM\JoinColumn(name: 'id_utilisateur', referencedColumnName: 'id_utilisateur', nullable: false)]
    private ?Utilisateur $utilisateur = null;

    #[ORM\Column(name: 'Localisation', length: 100)]
    #[Assert\NotBlank(message: 'La localisation est obligatoire.')]
    #[Assert\Length(
        min: 3,
        max: 100,
        minMessage: 'La localisation doit contenir au moins {{ limit }} caracteres.',
        maxMessage: 'La localisation ne peut pas depasser {{ limit }} caracteres.'
    )]
    private ?string $localisation = null;

    #[ORM\Column(name: 'Longueur')]
    #[Assert\NotBlank(message: 'La longueur est obligatoire.')]
    #[Assert\Positive(message: 'La longueur doit etre un nombre positif.')]
    private ?float $longueur = null;

    #[ORM\Column(name: 'Largeur')]
    #[Assert\NotBlank(message: 'La largeur est obligatoire.')]
    #[Assert\Positive(message: 'La largeur doit etre un nombre positif.')]
    private ?float $largeur = null;

    #[ORM\Column(name: 'TypeDeSol', length: 50)]
    #[Assert\NotBlank(message: 'Le type de sol est obligatoire.')]
    #[Assert\Length(
        max: 25,
        maxMessage: 'Le type de sol ne peut pas depasser {{ limit }} caracteres.'
    )]
    private ?string $typeDeSol = null;

    public function getIdParcelle(): ?int
    {
        return $this->idParcelle;
    }

    public function getProprietaire(): ?string
    {
        return $this->proprietaire;
    }

    public function setProprietaire(string $proprietaire): static
    {
        $this->proprietaire = $proprietaire;

        return $this;
    }

    public function getUtilisateur(): ?Utilisateur
    {
        return $this->utilisateur;
    }

    public function setUtilisateur(?Utilisateur $utilisateur): static
    {
        $this->utilisateur = $utilisateur;

        return $this;
    }

    public function getLocalisation(): ?string
    {
        return $this->localisation;
    }

    public function setLocalisation(string $localisation): static
    {
        $this->localisation = $localisation;

        return $this;
    }

    public function getLongueur(): ?float
    {
        return $this->longueur;
    }

    public function setLongueur(float $longueur): static
    {
        $this->longueur = $longueur;

        return $this;
    }

    public function getLargeur(): ?float
    {
        return $this->largeur;
    }

    public function setLargeur(float $largeur): static
    {
        $this->largeur = $largeur;

        return $this;
    }

    public function getTypeDeSol(): ?string
    {
        return $this->typeDeSol;
    }

    public function setTypeDeSol(string $typeDeSol): static
    {
        $this->typeDeSol = $typeDeSol;

        return $this;
    }

    public function __toString(): string
    {
        return $this->proprietaire ?: 'New Parcelle';
    }

    public function getSuperficie(): ?float
    {
        if ($this->longueur && $this->largeur) {
            return $this->longueur * $this->largeur;
        }

        return null;
    }
}
