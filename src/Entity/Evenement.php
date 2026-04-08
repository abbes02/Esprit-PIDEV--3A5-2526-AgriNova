<?php

namespace App\Entity;

use App\Repository\EvenementRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: EvenementRepository::class)]
#[ORM\Table(name: 'evenement')]
class Evenement
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'IdEvenement')]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'evenements')]
    #[ORM\JoinColumn(name: 'IdFormation', referencedColumnName: 'IdFormation', nullable: false)]
    #[Assert\NotNull(message: 'Une formation doit etre selectionnee.')]
    private ?Formation $formation = null;

    #[ORM\Column(name: 'DateDebut', type: Types::DATETIME_MUTABLE, nullable: true)]
    #[Assert\NotNull]
    private ?\DateTimeInterface $dateDebut = null;

    #[ORM\Column(name: 'DateFin', type: Types::DATETIME_MUTABLE, nullable: true)]
    #[Assert\NotNull]
    #[Assert\GreaterThan(propertyPath: 'dateDebut', message: 'La date de fin doit etre apres la date de debut.')]
    private ?\DateTimeInterface $dateFin = null;

    #[ORM\Column(name: 'Lieu', length: 200, nullable: true)]
    #[Assert\NotBlank]
    private ?string $lieu = null;

    #[ORM\Column(name: 'Type', length: 50, nullable: true)]
    #[Assert\NotBlank]
    private ?string $type = null;

    #[ORM\Column(name: 'CapaciteMax', nullable: true)]
    #[Assert\Positive(message: 'La capacite doit etre superieure a 0.')]
    private ?int $capaciteMax = null;

    #[ORM\Column(name: 'NombreInscrits', options: ['default' => 0])]
    #[Assert\PositiveOrZero]
    private int $nombreInscrits = 0;

    #[ORM\Column(name: 'Statut', length: 50, nullable: true)]
    #[Assert\NotBlank]
    private ?string $statut = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFormation(): ?Formation
    {
        return $this->formation;
    }

    public function setFormation(?Formation $formation): static
    {
        $this->formation = $formation;

        return $this;
    }

    public function getDateDebut(): ?\DateTimeInterface
    {
        return $this->dateDebut;
    }

    public function setDateDebut(?\DateTimeInterface $dateDebut): static
    {
        $this->dateDebut = $dateDebut;

        return $this;
    }

    public function getDateFin(): ?\DateTimeInterface
    {
        return $this->dateFin;
    }

    public function setDateFin(?\DateTimeInterface $dateFin): static
    {
        $this->dateFin = $dateFin;

        return $this;
    }

    public function getLieu(): ?string
    {
        return $this->lieu;
    }

    public function setLieu(?string $lieu): static
    {
        $this->lieu = $lieu;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getCapaciteMax(): ?int
    {
        return $this->capaciteMax;
    }

    public function setCapaciteMax(?int $capaciteMax): static
    {
        $this->capaciteMax = $capaciteMax;

        return $this;
    }

    public function getNombreInscrits(): int
    {
        return $this->nombreInscrits;
    }

    public function setNombreInscrits(int $nombreInscrits): static
    {
        $this->nombreInscrits = $nombreInscrits;

        return $this;
    }

    public function getStatut(): ?string
    {
        return $this->statut;
    }

    public function setStatut(?string $statut): static
    {
        $this->statut = $statut;

        return $this;
    }
}
