<?php

namespace App\Entity;

use App\Repository\ProduitRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Entite parent (JOINED). Les tables filles portent les champs specifiques.
 * La colonne TypeProduit existante sert de discriminant.
 */
#[ORM\Entity(repositoryClass: ProduitRepository::class)]
#[ORM\Table(name: 'produit')]
#[ORM\InheritanceType('JOINED')]
#[ORM\DiscriminatorColumn(name: 'TypeProduit', type: 'string')]
#[ORM\DiscriminatorMap([
    'Agricole' => ProduitAgricole::class,
    'IOT' => ProduitIOT::class,
])]
abstract class Produit
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'IdProduit', type: 'integer')]
    private ?int $idProduit = null;

    #[ORM\Column(name: 'Ref', type: 'string', length: 50)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 50)]
    private string $ref;

    #[ORM\Column(name: 'Nom', type: 'string', length: 100)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 100)]
    private string $nom;

    #[ORM\Column(name: 'Image', type: 'string', length: 255, nullable: true)]
    #[Assert\Length(max: 255)]
    private ?string $image = null;

    #[ORM\Column(name: 'Description', type: 'text', nullable: true)]
    private ?string $description = null;

    #[ORM\Column(name: 'Quantite', type: 'integer', nullable: true)]
    #[Assert\PositiveOrZero]
    private ?int $quantite = null;

    #[ORM\Column(name: 'Prix', type: 'float', nullable: true)]
    #[Assert\Positive]
    private ?float $prix = null;

    #[ORM\Column(name: 'NbrLike', type: 'integer', options: ['default' => 0], nullable: true)]
    #[Assert\PositiveOrZero]
    private ?int $nbrLike = 0;

    #[ORM\Column(name: 'DateAjout', type: 'datetime', nullable: true, options: ['default' => 'CURRENT_TIMESTAMP'])]
    #[Assert\DateTime]
    private ?\DateTimeInterface $dateAjout = null;

    #[ORM\Column(name: 'Proprietaire', type: 'integer', nullable: true)]
    private ?int $proprietaire = null;

    public function __toString(): string
    {
        return $this->nom ?? '';
    }

    public function getTypeLabel(): string
    {
        if ($this instanceof ProduitAgricole) {
            return 'Agricole';
        }

        if ($this instanceof ProduitIOT) {
            return 'IOT';
        }

        return 'Produit';
    }

    public function getId(): ?int
    {
        return $this->idProduit;
    }

    public function getIdProduit(): ?int
    {
        return $this->idProduit;
    }

    public function getRef(): string
    {
        return $this->ref;
    }

    public function setRef(string $ref): self
    {
        $this->ref = $ref;

        return $this;
    }

    public function getNom(): string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): self
    {
        $this->image = $image;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getQuantite(): ?int
    {
        return $this->quantite;
    }

    public function setQuantite(?int $quantite): self
    {
        $this->quantite = $quantite;

        return $this;
    }

    public function getPrix(): ?float
    {
        return $this->prix;
    }

    public function setPrix(?float $prix): self
    {
        $this->prix = $prix;

        return $this;
    }

    public function getNbrLike(): ?int
    {
        return $this->nbrLike;
    }

    public function setNbrLike(?int $nbrLike): self
    {
        $this->nbrLike = $nbrLike;

        return $this;
    }

    public function getDateAjout(): ?\DateTimeInterface
    {
        return $this->dateAjout;
    }

    public function setDateAjout(?\DateTimeInterface $dateAjout): self
    {
        $this->dateAjout = $dateAjout;

        return $this;
    }

    public function getProprietaire(): ?int
    {
        return $this->proprietaire;
    }

    public function setProprietaire(?int $proprietaire): self
    {
        $this->proprietaire = $proprietaire;

        return $this;
    }
}
