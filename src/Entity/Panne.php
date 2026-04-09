<?php

namespace App\Entity;

use App\Repository\PanneRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

#[ORM\Entity(repositoryClass: PanneRepository::class)]
#[ORM\Table(name: 'panne_web', indexes: [new ORM\Index(name: 'fk_panne_web_materiel', columns: ['materiel_id'])])]
class Panne
{
    // Severity levels
    public const SEVERITY_MINOR = 'minor';
    public const SEVERITY_MAJOR = 'major';
    public const SEVERITY_CRITICAL = 'critical';

    public const SEVERITY_VALUES = [
        self::SEVERITY_MINOR,
        self::SEVERITY_MAJOR,
        self::SEVERITY_CRITICAL,
    ];

    public const SEVERITY_LABELS = [
        self::SEVERITY_MINOR => 'Mineur',
        self::SEVERITY_MAJOR => 'Majeur',
        self::SEVERITY_CRITICAL => 'Critique',
    ];

    // Panne types/categories
    public const TYPE_MECHANICAL = 'mechanical';
    public const TYPE_ELECTRICAL = 'electrical';
    public const TYPE_HYDRAULIC = 'hydraulic';
    public const TYPE_STRUCTURAL = 'structural';
    public const TYPE_ENGINE = 'engine';
    public const TYPE_TIRE = 'tire';
    public const TYPE_OTHER = 'other';

    public const TYPE_VALUES = [
        self::TYPE_MECHANICAL,
        self::TYPE_ELECTRICAL,
        self::TYPE_HYDRAULIC,
        self::TYPE_STRUCTURAL,
        self::TYPE_ENGINE,
        self::TYPE_TIRE,
        self::TYPE_OTHER,
    ];

    public const TYPE_LABELS = [
        self::TYPE_MECHANICAL => 'Mécanique',
        self::TYPE_ELECTRICAL => 'Électrique',
        self::TYPE_HYDRAULIC => 'Hydraulique',
        self::TYPE_STRUCTURAL => 'Structure',
        self::TYPE_ENGINE => 'Moteur',
        self::TYPE_TIRE => 'Pneu/Roue',
        self::TYPE_OTHER => 'Autre',
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\Column(name: 'description_panne', type: Types::TEXT)]
    #[Assert\NotBlank(message: 'La description de la panne est obligatoire.')]
    #[Assert\Length(
        min: 10,
        max: 2000,
        minMessage: 'La description doit contenir au moins {{ limit }} caractères.',
        maxMessage: 'La description ne peut pas dépasser {{ limit }} caractères.'
    )]
    private ?string $descriptionPanne = null;

    #[ORM\Column(name: 'date_panne', type: Types::DATE_MUTABLE)]
    #[Assert\NotBlank(message: 'La date de panne est obligatoire.')]
    #[Assert\Type(\DateTimeInterface::class, message: 'La date de panne n\'est pas valide.')]
    #[Assert\LessThanOrEqual(
        value: 'today',
        message: 'La date de panne ne peut pas être dans le futur.'
    )]
    private ?\DateTimeInterface $datePanne = null;

    #[ORM\Column(name: 'date_reparation', type: Types::DATE_MUTABLE, nullable: true)]
    #[Assert\Type(\DateTimeInterface::class, message: 'La date de réparation n\'est pas valide.')]
    #[Assert\GreaterThanOrEqual(
        propertyPath: 'datePanne',
        message: 'La date de réparation doit être supérieure ou égale à la date de panne.'
    )]
    private ?\DateTimeInterface $dateReparation = null;

    #[ORM\Column(name: 'cout_reparation', type: Types::FLOAT, nullable: true, options: ['default' => 0])]
    #[Assert\PositiveOrZero(message: 'Le coût de réparation doit être un nombre positif ou zéro.')]
    #[Assert\LessThanOrEqual(
        value: 1000000,
        message: 'Le coût de réparation ne peut pas dépasser {{ compared_value }} TND.'
    )]
    private ?float $coutReparation = 0;

    #[ORM\ManyToOne(targetEntity: Materiel::class, inversedBy: 'pannes')]
    #[ORM\JoinColumn(name: 'materiel_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    #[Assert\NotNull(message: 'Le matériel concerné est obligatoire.')]
    private ?Materiel $materiel = null;

    #[ORM\Column(name: 'image_url', type: Types::STRING, length: 512, nullable: true)]
    #[Assert\Length(
        max: 512,
        maxMessage: 'L\'URL de l\'image ne peut pas dépasser {{ limit }} caractères.'
    )]
    private ?string $imageUrl = null;

    #[ORM\Column(name: 'diagnostic_solution', type: Types::TEXT, nullable: true)]
    #[Assert\Length(
        max: 2000,
        maxMessage: 'Le diagnostic/solution ne peut pas dépasser {{ limit }} caractères.'
    )]
    private ?string $diagnosticSolution = null;

    #[ORM\Column(name: 'severity', type: Types::STRING, length: 20, nullable: true, options: ['default' => 'minor'])]
    #[Assert\Choice(
        choices: self::SEVERITY_VALUES,
        message: 'Le niveau de gravité sélectionné n\'est pas valide.'
    )]
    private ?string $severity = self::SEVERITY_MINOR;

    #[ORM\Column(name: 'panne_type', type: Types::STRING, length: 30, nullable: true, options: ['default' => 'other'])]
    #[Assert\Choice(
        choices: self::TYPE_VALUES,
        message: 'Le type de panne sélectionné n\'est pas valide.'
    )]
    private ?string $panneType = self::TYPE_OTHER;

    #[ORM\Column(name: 'priority', type: Types::INTEGER, nullable: true, options: ['default' => 3])]
    #[Assert\Range(
        min: 1,
        max: 5,
        notInRangeMessage: 'La priorité doit être comprise entre {{ min }} et {{ max }}.'
    )]
    private ?int $priority = 3;

    #[ORM\Column(name: 'estimated_cost', type: Types::FLOAT, nullable: true)]
    #[Assert\PositiveOrZero(message: 'Le coût estimé doit être un nombre positif ou zéro.')]
    #[Assert\LessThanOrEqual(
        value: 1000000,
        message: 'Le coût estimé ne peut pas dépasser {{ compared_value }} TND.'
    )]
    private ?float $estimatedCost = null;

    #[ORM\Column(name: 'reported_by_name', type: Types::STRING, length: 255, nullable: true)]
    #[Assert\Length(
        max: 255,
        maxMessage: 'Le nom du déclarant ne peut pas dépasser {{ limit }} caractères.'
    )]
    private ?string $reportedByName = null;

    #[Assert\Callback]

    public function validateRepairDate(ExecutionContextInterface $context): void
    {
        $today = new \DateTimeImmutable('today');

        if ($this->datePanne && $this->datePanne > $today) {
            $context->buildViolation('La date de panne ne peut pas etre dans le futur.')
                ->atPath('datePanne')
                ->addViolation();
        }

        if ($this->datePanne && $this->dateReparation && $this->dateReparation < $this->datePanne) {
            $context->buildViolation('La date de reparation doit etre superieure ou egale a la date de panne.')
                ->atPath('dateReparation')
                ->addViolation();
        }
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDescriptionPanne(): ?string
    {
        return $this->descriptionPanne;
    }

    public function setDescriptionPanne(string $descriptionPanne): self
    {
        $this->descriptionPanne = $descriptionPanne;

        return $this;
    }

    public function getDatePanne(): ?\DateTimeInterface
    {
        return $this->datePanne;
    }

    public function setDatePanne(\DateTimeInterface $datePanne): self
    {
        $this->datePanne = $datePanne;

        return $this;
    }

    public function getDateReparation(): ?\DateTimeInterface
    {
        return $this->dateReparation;
    }

    public function setDateReparation(?\DateTimeInterface $dateReparation): self
    {
        $this->dateReparation = $dateReparation;

        return $this;
    }

    public function getCoutReparation(): ?float
    {
        return $this->coutReparation;
    }

    public function setCoutReparation(?float $coutReparation): self
    {
        $this->coutReparation = $coutReparation;

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

    public function getImageUrl(): ?string
    {
        return $this->imageUrl;
    }

    public function setImageUrl(?string $imageUrl): self
    {
        $this->imageUrl = $imageUrl;

        return $this;
    }

    public function getDiagnosticSolution(): ?string
    {
        return $this->diagnosticSolution;
    }

    public function setDiagnosticSolution(?string $diagnosticSolution): self
    {
        $this->diagnosticSolution = $diagnosticSolution;

        return $this;
    }

    public function getSeverity(): ?string
    {
        return $this->severity;
    }

    public function setSeverity(?string $severity): self
    {
        $this->severity = $severity;

        return $this;
    }

    public function getSeverityLabel(): string
    {
        return self::SEVERITY_LABELS[$this->severity] ?? 'Inconnu';
    }

    public function getPanneType(): ?string
    {
        return $this->panneType;
    }

    public function setPanneType(?string $panneType): self
    {
        $this->panneType = $panneType;

        return $this;
    }

    public function getPanneTypeLabel(): string
    {
        return self::TYPE_LABELS[$this->panneType] ?? 'Autre';
    }

    public function getPriority(): ?int
    {
        return $this->priority;
    }

    public function setPriority(?int $priority): self
    {
        $this->priority = max(1, min(5, $priority ?? 3));

        return $this;
    }

    public function getEstimatedCost(): ?float
    {
        return $this->estimatedCost;
    }

    public function setEstimatedCost(?float $estimatedCost): self
    {
        $this->estimatedCost = $estimatedCost;

        return $this;
    }

    public function getReportedByName(): ?string
    {
        return $this->reportedByName;
    }

    public function setReportedByName(?string $reportedByName): self
    {
        $this->reportedByName = $reportedByName;

        return $this;
    }

    public function isResolved(): bool
    {
        return $this->dateReparation !== null;
    }

    public function isCritical(): bool
    {
        return $this->severity === self::SEVERITY_CRITICAL;
    }
}
