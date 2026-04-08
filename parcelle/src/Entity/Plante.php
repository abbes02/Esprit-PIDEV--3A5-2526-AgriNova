<?php

namespace App\Entity;

use App\Entity\Parcelle;
use App\Repository\PlanteRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: PlanteRepository::class)]
class Plante
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'IdPlante')]
    private ?int $idPlante = null;

    #[ORM\ManyToOne(targetEntity: Parcelle::class)]
    #[ORM\JoinColumn(name: 'IdParcelle', referencedColumnName: 'IdParcelle', nullable: false)]
    private ?Parcelle $parcelle = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: 'Le nom est obligatoire.')]
    #[Assert\Length(
        min: 3,
        max: 100,
        minMessage: 'Le nom doit contenir au moins {{ limit }} caractères.',
        maxMessage: 'Le nom ne peut pas dépasser {{ limit }} caractères.'
    )]
    private ?string $nom = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(message: 'Le type est obligatoire.')]
    #[Assert\Length(
        max: 50,
        maxMessage: 'Le type ne peut pas dépasser {{ limit }} caractères.'
    )]
    private ?string $type = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $image = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: 'La quantité est obligatoire.')]
    #[Assert\Positive(message: 'La quantité doit être un nombre positif.')]
    private ?int $quantite = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: 'La surface est obligatoire.')]
    #[Assert\Positive(message: 'La surface doit être un nombre positif.')]
    private ?float $surface = null;

    public function getIdPlante(): ?int
    {
        return $this->idPlante;
    }

    public function getParcelle(): ?Parcelle
    {
        return $this->parcelle;
    }

    public function setParcelle(?Parcelle $parcelle): static
    {
        $this->parcelle = $parcelle;

        return $this;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): static
    {
        $this->image = $image;

        return $this;
    }

    public function getQuantite(): ?int
    {
        return $this->quantite;
    }

    public function setQuantite(int $quantite): static
    {
        $this->quantite = $quantite;

        return $this;
    }

    public function getSurface(): ?float
    {
        return $this->surface;
    }

    public function setSurface(float $surface): static
    {
        $this->surface = $surface;

        return $this;
    }

    public function __toString(): string
    {
        return $this->nom ?: 'New Plante';
    }
}
?>
