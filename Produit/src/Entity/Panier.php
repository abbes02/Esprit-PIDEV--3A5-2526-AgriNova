<?php

namespace App\Entity;

use App\Repository\PanierRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PanierRepository::class)]
#[ORM\Table(name: 'panier')]
class Panier
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id_panier', type: 'integer')]
    private ?int $idPanier = null;

    #[ORM\Column(name: 'id_utilisateur', type: 'integer')]
    private int $idUtilisateur;

    #[ORM\Column(name: 'id_produit', type: 'integer')]
    private int $idProduit;

    #[ORM\Column(name: 'quantite', type: 'integer', options: ['default' => 1])]
    private int $quantite = 1;

    #[ORM\Column(name: 'date_ajout', type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $dateAjout = null;

    #[ORM\Column(name: 'historique', type: 'string', length: 10, nullable: true)]
    private ?string $historique = 'show';

    public function getId(): ?int
    {
        return $this->idPanier;
    }

    public function getIdPanier(): ?int
    {
        return $this->idPanier;
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

    public function getIdProduit(): int
    {
        return $this->idProduit;
    }

    public function setIdProduit(int $idProduit): self
    {
        $this->idProduit = $idProduit;

        return $this;
    }

    public function getQuantite(): int
    {
        return $this->quantite;
    }

    public function setQuantite(int $quantite): self
    {
        $this->quantite = $quantite;

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

    public function getHistorique(): ?string
    {
        return $this->historique;
    }

    public function setHistorique(?string $historique): self
    {
        $this->historique = $historique;

        return $this;
    }
}
