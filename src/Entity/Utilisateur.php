<?php

namespace App\Entity;

use App\Repository\UtilisateurRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UtilisateurRepository::class)]
#[ORM\Table(name: 'utilisateur')]
class Utilisateur
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id_utilisateur')]
    private ?int $idUtilisateur = null;

    #[ORM\Column(name: 'nom', length: 100)]
    #[Assert\NotBlank(message: 'Le nom est obligatoire.')]
    #[Assert\Length(
        min: 2,
        max: 100,
        minMessage: 'Le nom doit contenir au moins {{ limit }} caracteres.',
        maxMessage: 'Le nom ne peut pas depasser {{ limit }} caracteres.'
    )]
    private ?string $nom = null;

    #[ORM\Column(name: 'prenom', length: 100)]
    #[Assert\NotBlank(message: 'Le prenom est obligatoire.')]
    #[Assert\Length(
        min: 2,
        max: 100,
        minMessage: 'Le prenom doit contenir au moins {{ limit }} caracteres.',
        maxMessage: 'Le prenom ne peut pas depasser {{ limit }} caracteres.'
    )]
    private ?string $prenom = null;

    #[ORM\Column(name: 'email', length: 150)]
    #[Assert\NotBlank(message: 'L email est obligatoire.')]
    #[Assert\Email(message: 'Veuillez saisir une adresse email valide.')]
    #[Assert\Length(
        max: 150,
        maxMessage: 'L email ne peut pas depasser {{ limit }} caracteres.'
    )]
    private ?string $email = null;

    #[ORM\Column(name: 'mot_de_passe', length: 255)]
    #[Assert\NotBlank(message: 'Le mot de passe est obligatoire.')]
    #[Assert\Length(
        min: 6,
        minMessage: 'Le mot de passe doit contenir au moins {{ limit }} caracteres.'
    )]
    #[Assert\Regex(
        pattern: '/^(?=.*[A-Z])(?=.*[^a-zA-Z0-9]).+$/',
        message: 'Le mot de passe doit contenir au moins une majuscule et un caractere special.'
    )]
    private ?string $motDePasse = null;

    #[ORM\Column(name: 'telephone', length: 20, nullable: true)]
    #[Assert\Length(
        max: 20,
        maxMessage: 'Le telephone ne peut pas depasser {{ limit }} caracteres.'
    )]
    private ?string $telephone = null;

    #[ORM\Column(name: 'adresse', length: 255, nullable: true)]
    #[Assert\Length(
        max: 255,
        maxMessage: 'L adresse ne peut pas depasser {{ limit }} caracteres.'
    )]
    private ?string $adresse = null;

    #[ORM\Column(name: 'role', length: 20)]
    #[Assert\NotBlank(message: 'Le role est obligatoire.')]
    #[Assert\Choice(
        choices: ['ADMIN', 'AGRICULTEUR', 'CLIENT', 'LIVREUR'],
        message: 'Le role doit etre parmi : ADMIN, AGRICULTEUR, CLIENT, LIVREUR.'
    )]
    private ?string $role = null;

    #[ORM\Column(name: 'statut', length: 20, options: ['default' => 'ACTIF'])]
    #[Assert\Choice(
        choices: ['ACTIF', 'DESACTIVE'],
        message: 'Le statut doit etre ACTIF ou DESACTIVE.'
    )]
    private string $statut = 'ACTIF';

    #[ORM\Column(name: 'reset_token', length: 255, nullable: true)]
    #[Assert\Length(
        max: 255,
        maxMessage: 'Le token de reinitialisation ne peut pas depasser {{ limit }} caracteres.'
    )]
    private ?string $resetToken = null;

    #[ORM\Column(name: 'points_cadeaux', options: ['default' => 0])]
    #[Assert\PositiveOrZero(message: 'Les points cadeaux doivent etre superieurs ou egaux a zero.')]
    private int $pointsCadeaux = 0;

    #[ORM\Column(name: 'date_creation', nullable: true)]
    private ?\DateTimeImmutable $dateCreation = null;

    public function getIdUtilisateur(): ?int
    {
        return $this->idUtilisateur;
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

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): static
    {
        $this->prenom = $prenom;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getMotDePasse(): ?string
    {
        return $this->motDePasse;
    }

    public function setMotDePasse(string $motDePasse): static
    {
        $this->motDePasse = $motDePasse;

        return $this;
    }

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(?string $telephone): static
    {
        $this->telephone = $telephone;

        return $this;
    }

    public function getAdresse(): ?string
    {
        return $this->adresse;
    }

    public function setAdresse(?string $adresse): static
    {
        $this->adresse = $adresse;

        return $this;
    }

    public function getRole(): ?string
    {
        return $this->role;
    }

    public function setRole(string $role): static
    {
        $this->role = strtoupper($role);

        return $this;
    }

    public function getStatut(): string
    {
        return $this->statut;
    }

    public function setStatut(string $statut): static
    {
        $this->statut = strtoupper($statut);

        return $this;
    }

    public function getResetToken(): ?string
    {
        return $this->resetToken;
    }

    public function setResetToken(?string $resetToken): static
    {
        $this->resetToken = $resetToken;

        return $this;
    }

    public function getPointsCadeaux(): int
    {
        return $this->pointsCadeaux;
    }

    public function setPointsCadeaux(int $pointsCadeaux): static
    {
        $this->pointsCadeaux = $pointsCadeaux;

        return $this;
    }

    public function getDateCreation(): ?\DateTimeImmutable
    {
        return $this->dateCreation;
    }

    public function setDateCreation(?\DateTimeImmutable $dateCreation): static
    {
        $this->dateCreation = $dateCreation;

        return $this;
    }

    public function getDisplayName(): string
    {
        return trim((string) $this->prenom . ' ' . (string) $this->nom);
    }
}
