<?php

namespace App\Entity;

use App\Repository\EvenementRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: EvenementRepository::class)]
class Evenement
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'evenements')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: 'A formation must be selected.')]
    private ?Formation $formation = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Assert\NotNull]
    private ?\DateTimeInterface $dateDebut = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Assert\NotNull]
    #[Assert\GreaterThan(propertyPath: 'dateDebut', message: 'End date must be after start date.')]
    private ?\DateTimeInterface $dateFin = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    private ?string $lieu = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank]
    private ?string $type = null;

    #[ORM\Column]
    #[Assert\Positive(message: 'Capacity must be greater than 0.')]
    private ?int $capaciteMax = null;

    #[ORM\Column]
    #[Assert\PositiveOrZero]
    private int $nombreInscrits = 0;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank]
    private ?string $statut = null;

    public function getId(): ?int { return $this->id; }

    public function getFormation(): ?Formation { return $this->formation; }
    public function setFormation(?Formation $formation): static { $this->formation = $formation; return $this; }

    public function getDateDebut(): ?\DateTimeInterface { return $this->dateDebut; }
    public function setDateDebut(\DateTimeInterface $dateDebut): static { $this->dateDebut = $dateDebut; return $this; }

    public function getDateFin(): ?\DateTimeInterface { return $this->dateFin; }
    public function setDateFin(\DateTimeInterface $dateFin): static { $this->dateFin = $dateFin; return $this; }

    public function getLieu(): ?string { return $this->lieu; }
    public function setLieu(string $lieu): static { $this->lieu = $lieu; return $this; }

    public function getType(): ?string { return $this->type; }
    public function setType(string $type): static { $this->type = $type; return $this; }

    public function getCapaciteMax(): ?int { return $this->capaciteMax; }
    public function setCapaciteMax(int $capaciteMax): static { $this->capaciteMax = $capaciteMax; return $this; }

    public function getNombreInscrits(): int { return $this->nombreInscrits; }
    public function setNombreInscrits(int $nombreInscrits): static { $this->nombreInscrits = $nombreInscrits; return $this; }

    public function getStatut(): ?string { return $this->statut; }
    public function setStatut(string $statut): static { $this->statut = $statut; return $this; }
}
