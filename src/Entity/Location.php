<?php

namespace App\Entity;

use App\Repository\LocationRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

#[ORM\Entity(repositoryClass: LocationRepository::class)]
#[ORM\Table(name: 'location', indexes: [
    new ORM\Index(name: 'fk_loc_materiel', columns: ['materiel_id']),
    new ORM\Index(name: 'fk_loc_user', columns: ['utilisateur_id']),
])]
class Location
{
    public const STATUT_EN_COURS = 'En cours';
    public const STATUT_TERMINEE = 'Terminee';
    public const STATUT_ANNULEE = 'Annulee';

    public const STATUT_VALUES = [
        self::STATUT_EN_COURS,
        self::STATUT_TERMINEE,
        self::STATUT_ANNULEE,
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\Column(name: 'date_debut', type: Types::DATE_MUTABLE)]
    #[Assert\NotBlank(message: 'La date de début est obligatoire.')]
    #[Assert\Type(\DateTimeInterface::class, message: 'La date de début n\'est pas valide.')]
    private ?\DateTimeInterface $dateDebut = null;

    #[ORM\Column(name: 'date_fin', type: Types::DATE_MUTABLE)]
    #[Assert\NotBlank(message: 'La date de fin est obligatoire.')]
    #[Assert\Type(\DateTimeInterface::class, message: 'La date de fin n\'est pas valide.')]
    #[Assert\GreaterThanOrEqual(
        propertyPath: 'dateDebut',
        message: 'La date de fin doit être supérieure ou égale à la date de début.'
    )]
    private ?\DateTimeInterface $dateFin = null;

    #[ORM\Column(name: 'montant_total', type: Types::FLOAT)]
    #[Assert\NotBlank(message: 'Le montant total est obligatoire.')]
    #[Assert\Positive(message: 'Le montant total doit être un nombre positif.')]
    private ?float $montantTotal = null;

    #[ORM\Column(name: 'statut', type: Types::STRING, length: 50, nullable: true, options: ['default' => 'En cours'])]
    #[Assert\Choice(
        choices: self::STATUT_VALUES,
        message: 'Le statut sélectionné n\'est pas valide.'
    )]
    private ?string $statut = self::STATUT_EN_COURS;

    #[ORM\ManyToOne(targetEntity: Materiel::class, inversedBy: 'locations')]
    #[ORM\JoinColumn(name: 'materiel_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    #[Assert\NotNull(message: 'Le matériel est obligatoire.')]
    private ?Materiel $materiel = null;

    #[ORM\ManyToOne(targetEntity: Utilisateur::class, inversedBy: 'locations')]
    #[ORM\JoinColumn(name: 'utilisateur_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    #[Assert\NotNull(message: 'L\'utilisateur est obligatoire.')]
    private ?Utilisateur $utilisateur = null;

    #[Assert\Callback]

    public function validateDateRange(ExecutionContextInterface $context): void
    {
        $today = new \DateTimeImmutable('today');

        if ($this->id === null && $this->dateDebut && $this->dateDebut < $today) {
            $context->buildViolation('La date de debut doit etre aujourd\'hui ou dans le futur.')
                ->atPath('dateDebut')
                ->addViolation();
        }

        if ($this->dateDebut && $this->dateFin && $this->dateFin < $this->dateDebut) {
            $context->buildViolation('La date de fin doit etre superieure ou egale a la date de debut.')
                ->atPath('dateFin')
                ->addViolation();
        }

        if ($this->id === null && $this->materiel && $this->materiel->getEtat() !== Materiel::ETAT_DISPONIBLE) {
            $context->buildViolation('Le materiel selectionne n\'est pas disponible actuellement.')
                ->atPath('materiel')
                ->addViolation();
        }

        if ($this->materiel && $this->montantTotal !== null) {
            $minimumAmount = (float) ($this->materiel->getPrixLocation() ?? 0.0);

            if ($minimumAmount > 0 && $this->montantTotal < $minimumAmount) {
                $context->buildViolation('Le montant total doit etre superieur ou egal au prix de location du materiel.')
                    ->atPath('montantTotal')
                    ->addViolation();
            }
        }
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDateDebut(): ?\DateTimeInterface
    {
        return $this->dateDebut;
    }

    public function setDateDebut(\DateTimeInterface $dateDebut): self
    {
        $this->dateDebut = $dateDebut;

        return $this;
    }

    public function getDateFin(): ?\DateTimeInterface
    {
        return $this->dateFin;
    }

    public function setDateFin(\DateTimeInterface $dateFin): self
    {
        $this->dateFin = $dateFin;

        return $this;
    }

    public function getMontantTotal(): ?float
    {
        return $this->montantTotal;
    }

    public function setMontantTotal(float $montantTotal): self
    {
        $this->montantTotal = $montantTotal;

        return $this;
    }

    public function getStatut(): ?string
    {
        return $this->statut;
    }

    public function setStatut(?string $statut): self
    {
        $this->statut = $statut;

        return $this;
    }

    public function getMateriel(): ?Materiel
    {
        return $this->materiel;
    }

    public function setMateriel(?Materiel $materiel): self
    {
        $this->materiel = $materiel;

        return $this;
    }

    public function getUtilisateur(): ?Utilisateur
    {
        return $this->utilisateur;
    }

    public function setUtilisateur(?Utilisateur $utilisateur): self
    {
        $this->utilisateur = $utilisateur;

        return $this;
    }
}
