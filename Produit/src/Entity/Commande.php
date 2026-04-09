<?php

namespace App\Entity;

use App\Repository\CommandeRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CommandeRepository::class)]
#[ORM\Table(name: 'commande')]
class Commande
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id_commande', type: 'integer')]
    private ?int $idCommande = null;

    #[ORM\Column(name: 'id_panier', type: 'integer')]
    private int $idPanier;

    #[ORM\Column(name: 'id_utilisateur', type: 'integer')]
    private int $idUtilisateur;

    #[ORM\Column(name: 'id_livreur', type: 'integer', nullable: true)]
    private ?int $idLivreur = null;

    #[ORM\Column(name: 'date_commande', type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $dateCommande = null;

    #[ORM\Column(name: 'etat', type: 'string', length: 30, nullable: true)]
    private ?string $etat = 'cherche_livraison';

    #[ORM\Column(name: 'date_livraison', type: 'date', nullable: true)]
    private ?\DateTimeInterface $dateLivraison = null;

    #[ORM\Column(name: 'localisation', type: 'string', length: 255, nullable: true)]
    private ?string $localisation = null;

    #[ORM\Column(name: 'telephone_client', type: 'string', length: 20, nullable: true)]
    private ?string $telephoneClient = null;

    #[ORM\Column(name: 'date_confirmation_livreur', type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $dateConfirmationLivreur = null;

    public function getId(): ?int
    {
        return $this->idCommande;
    }

    public function getIdCommande(): ?int
    {
        return $this->idCommande;
    }

    public function getIdPanier(): int
    {
        return $this->idPanier;
    }

    public function setIdPanier(int $idPanier): self
    {
        $this->idPanier = $idPanier;

        return $this;
    }

    public function getIdUtilisateur(): int
    {
        return $this->idUtilisateur;
    }

    public function setIdUtilisateur(int $idUtilisateur): self
    {
        $this->idUtilisateur = $idUtilisateur;

        return $this;
    }

    public function getIdLivreur(): ?int
    {
        return $this->idLivreur;
    }

    public function setIdLivreur(?int $idLivreur): self
    {
        $this->idLivreur = $idLivreur;

        return $this;
    }

    public function getDateCommande(): ?\DateTimeInterface
    {
        return $this->dateCommande;
    }

    public function setDateCommande(?\DateTimeInterface $dateCommande): self
    {
        $this->dateCommande = $dateCommande;

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

    public function getDateLivraison(): ?\DateTimeInterface
    {
        return $this->dateLivraison;
    }

    public function setDateLivraison(?\DateTimeInterface $dateLivraison): self
    {
        $this->dateLivraison = $dateLivraison;

        return $this;
    }

    public function getLocalisation(): ?string
    {
        return $this->localisation;
    }

    public function setLocalisation(?string $localisation): self
    {
        $this->localisation = $localisation;

        return $this;
    }

    public function getTelephoneClient(): ?string
    {
        return $this->telephoneClient;
    }

    public function setTelephoneClient(?string $telephoneClient): self
    {
        $this->telephoneClient = $telephoneClient;

        return $this;
    }

    public function getDateConfirmationLivreur(): ?\DateTimeInterface
    {
        return $this->dateConfirmationLivreur;
    }

    public function setDateConfirmationLivreur(?\DateTimeInterface $dateConfirmationLivreur): self
    {
        $this->dateConfirmationLivreur = $dateConfirmationLivreur;

        return $this;
    }
}
