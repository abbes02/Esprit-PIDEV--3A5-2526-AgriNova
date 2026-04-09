<?php

namespace App\Entity;

use App\Repository\MaterielRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: MaterielRepository::class)]
#[ORM\Table(name: 'materiel_web', indexes: [new ORM\Index(name: 'fk_materiel_web_proprietaire', columns: ['proprietaire_id'])])]
class Materiel
{
    public const ETAT_DISPONIBLE = 'Disponible';
    public const ETAT_LOUE = 'Loue';
    public const ETAT_EN_PANNE = 'En Panne';

    public const ETAT_VALUES = [
        self::ETAT_DISPONIBLE,
        self::ETAT_LOUE,
        self::ETAT_EN_PANNE,
    ];

    // Equipment types
    public const TYPE_TRACTOR = 'tractor';
    public const TYPE_HARVESTER = 'harvester';
    public const TYPE_SEEDER = 'seeder';
    public const TYPE_SPRAYER = 'sprayer';
    public const TYPE_PLOW = 'plow';
    public const TYPE_TRAILER = 'trailer';
    public const TYPE_PUMP = 'pump';
    public const TYPE_IRRIGATION = 'irrigation';
    public const TYPE_TOOL = 'tool';
    public const TYPE_OTHER = 'other';

    public const TYPE_VALUES = [
        self::TYPE_TRACTOR,
        self::TYPE_HARVESTER,
        self::TYPE_SEEDER,
        self::TYPE_SPRAYER,
        self::TYPE_PLOW,
        self::TYPE_TRAILER,
        self::TYPE_PUMP,
        self::TYPE_IRRIGATION,
        self::TYPE_TOOL,
        self::TYPE_OTHER,
    ];

    public const TYPE_LABELS = [
        self::TYPE_TRACTOR => 'Tracteur',
        self::TYPE_HARVESTER => 'Moissonneuse',
        self::TYPE_SEEDER => 'Semoir',
        self::TYPE_SPRAYER => 'Pulvérisateur',
        self::TYPE_PLOW => 'Charrue',
        self::TYPE_TRAILER => 'Remorque',
        self::TYPE_PUMP => 'Pompe',
        self::TYPE_IRRIGATION => 'Irrigation',
        self::TYPE_TOOL => 'Outillage',
        self::TYPE_OTHER => 'Autre',
    ];

    public const TYPE_ICONS = [
        self::TYPE_TRACTOR => 'fa-tractor',
        self::TYPE_HARVESTER => 'fa-wheat-awn',
        self::TYPE_SEEDER => 'fa-seedling',
        self::TYPE_SPRAYER => 'fa-spray-can',
        self::TYPE_PLOW => 'fa-mountain',
        self::TYPE_TRAILER => 'fa-truck-pickup',
        self::TYPE_PUMP => 'fa-faucet',
        self::TYPE_IRRIGATION => 'fa-droplet',
        self::TYPE_TOOL => 'fa-wrench',
        self::TYPE_OTHER => 'fa-box',
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\Column(name: 'nom', type: Types::STRING, length: 255)]
    #[Assert\NotBlank(message: 'Le nom du matériel est obligatoire.')]
    #[Assert\Length(
        min: 2,
        max: 255,
        minMessage: 'Le nom doit contenir au moins {{ limit }} caractères.',
        maxMessage: 'Le nom ne peut pas dépasser {{ limit }} caractères.'
    )]
    private ?string $nom = null;

    #[ORM\Column(name: 'type', type: Types::STRING, length: 100)]
    #[Assert\NotBlank(message: 'Le type de matériel est obligatoire.')]
    #[Assert\Choice(
        choices: self::TYPE_VALUES,
        message: 'Le type sélectionné n\'est pas valide.'
    )]
    private ?string $type = null;

    #[ORM\Column(name: 'description', type: Types::TEXT, nullable: true)]
    #[Assert\Length(
        max: 2000,
        maxMessage: 'La description ne peut pas dépasser {{ limit }} caractères.'
    )]
    private ?string $description = null;

    #[ORM\Column(name: 'prix_location', type: Types::FLOAT)]
    #[Assert\NotBlank(message: 'Le prix de location est obligatoire.')]
    #[Assert\Positive(message: 'Le prix de location doit être un nombre positif.')]
    #[Assert\LessThanOrEqual(
        value: 100000,
        message: 'Le prix de location ne peut pas dépasser {{ compared_value }} TND.'
    )]
    private ?float $prixLocation = null;

    #[ORM\Column(name: 'etat', type: Types::STRING, length: 50, nullable: true, options: ['default' => 'Disponible'])]
    #[Assert\Choice(
        choices: self::ETAT_VALUES,
        message: 'L\'état sélectionné n\'est pas valide.'
    )]
    private ?string $etat = self::ETAT_DISPONIBLE;

    #[ORM\Column(name: 'image_url', type: Types::STRING, length: 255, nullable: true)]
    #[Assert\Length(
        max: 255,
        maxMessage: 'L\'URL de l\'image ne peut pas dépasser {{ limit }} caractères.'
    )]
    private ?string $imageUrl = null;

    #[ORM\Column(name: 'date_ajout', type: Types::DATETIME_MUTABLE, nullable: true, options: ['default' => 'CURRENT_TIMESTAMP'])]
    private ?\DateTimeInterface $dateAjout = null;

    #[ORM\ManyToOne(targetEntity: Utilisateur::class, inversedBy: 'materiels')]
    #[ORM\JoinColumn(name: 'proprietaire_id', referencedColumnName: 'id_utilisateur', nullable: true)]
    private ?Utilisateur $proprietaire = null;

    /** @var Collection<int, Location> */
    #[ORM\OneToMany(mappedBy: 'materiel', targetEntity: Location::class)]
    private Collection $locations;

    /** @var Collection<int, Panne> */
    #[ORM\OneToMany(mappedBy: 'materiel', targetEntity: Panne::class)]
    private Collection $pannes;

    public function __construct()
    {
        $this->locations = new ArrayCollection();
        $this->pannes = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->nom ?? '';
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getTypeLabel(): string
    {
        return self::TYPE_LABELS[$this->type] ?? $this->type ?? 'Autre';
    }

    public function getTypeIcon(): string
    {
        return self::TYPE_ICONS[$this->type] ?? 'fa-box';
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

    public function getPrixLocation(): ?float
    {
        return $this->prixLocation;
    }

    public function setPrixLocation(float $prixLocation): self
    {
        $this->prixLocation = $prixLocation;

        return $this;
    }

    public function getEtat(): ?string
    {
        return $this->etat;
    }

    public function setEtat(?string $etat): self
    {
        $this->etat = $etat;

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

    public function getDateAjout(): ?\DateTimeInterface
    {
        return $this->dateAjout;
    }

    public function setDateAjout(?\DateTimeInterface $dateAjout): self
    {
        $this->dateAjout = $dateAjout;

        return $this;
    }

    public function getProprietaire(): ?Utilisateur
    {
        return $this->proprietaire;
    }

    public function setProprietaire(?Utilisateur $proprietaire): self
    {
        $this->proprietaire = $proprietaire;

        return $this;
    }

    /**
     * @return Collection<int, Location>
     */
    public function getLocations(): Collection
    {
        return $this->locations;
    }

    public function addLocation(Location $location): self
    {
        if (!$this->locations->contains($location)) {
            $this->locations->add($location);
            $location->setMateriel($this);
        }

        return $this;
    }

    public function removeLocation(Location $location): self
    {
        $this->locations->removeElement($location);

        return $this;
    }

    /**
     * @return Collection<int, Panne>
     */
    public function getPannes(): Collection
    {
        return $this->pannes;
    }

    public function addPanne(Panne $panne): self
    {
        if (!$this->pannes->contains($panne)) {
            $this->pannes->add($panne);
            $panne->setMateriel($this);
        }

        return $this;
    }

    public function removePanne(Panne $panne): self
    {
        $this->pannes->removeElement($panne);

        return $this;
    }

    public function getLatestOpenPanne(): ?Panne
    {
        $latestOpenPanne = null;

        foreach ($this->pannes as $panne) {
            if ($panne->getDateReparation() !== null) {
                continue;
            }

            if (!$latestOpenPanne instanceof Panne) {
                $latestOpenPanne = $panne;
                continue;
            }

            $candidateDate = $panne->getDatePanne();
            $latestDate = $latestOpenPanne->getDatePanne();

            if ($candidateDate instanceof \DateTimeInterface && $latestDate instanceof \DateTimeInterface) {
                if ($candidateDate > $latestDate) {
                    $latestOpenPanne = $panne;
                    continue;
                }

                if ($candidateDate == $latestDate && (int) ($panne->getId() ?? 0) > (int) ($latestOpenPanne->getId() ?? 0)) {
                    $latestOpenPanne = $panne;
                }

                continue;
            }

            if ($candidateDate instanceof \DateTimeInterface && !($latestDate instanceof \DateTimeInterface)) {
                $latestOpenPanne = $panne;
            }
        }

        return $latestOpenPanne;
    }
}
