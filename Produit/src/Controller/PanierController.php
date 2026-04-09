<?php

namespace App\Controller;

use Dompdf\Dompdf;
use Dompdf\Options;
use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PanierController extends AbstractController
{
    #[Route('/panier/nouveau', name: 'panier_page', methods: ['GET'])]
    public function panierPage(Connection $connection): Response
    {
        $userId = 6;
        $userRow = $connection->fetchAssociative(
            'SELECT adresse, telephone FROM utilisateur WHERE id_utilisateur = ?',
            [$userId]
        );
        $localisation = $userRow['adresse'] ?? null;
        $telephone = $userRow['telephone'] ?? null;
        $rows = $connection->fetchAllAssociative(
            'SELECT p.id_panier, p.id_produit, p.quantite, p.date_ajout, p.historique,
                    pr.Nom AS nom, pr.Prix AS prix, pr.Image AS image
             FROM panier p
             LEFT JOIN produit pr ON pr.IdProduit = p.id_produit
             WHERE p.id_utilisateur = ? AND (p.historique IS NULL OR p.historique = ?)
             ORDER BY p.date_ajout DESC',
            [$userId, 'show']
        );

        $total = 0.0;
        foreach ($rows as $row) {
            $prix = $row['prix'] !== null ? (float) $row['prix'] : 0.0;
            $qty = (int) $row['quantite'];
            $total += $prix * $qty;
        }

        return $this->render('panier/panier.html.twig', [
            'items' => $rows,
            'total' => $total,
            'localisation' => $localisation,
            'telephone' => $telephone,
        ]);
    }

    #[Route('/panier/historique', name: 'panier_historique', methods: ['GET'])]
    public function historique(Connection $connection): Response
    {
        $userId = 6;
        $rows = $connection->fetchAllAssociative(
            'SELECT p.id_panier, p.id_produit, p.quantite, p.date_ajout, p.historique,
                    pr.Nom AS nom, pr.Prix AS prix, pr.Image AS image
             FROM panier p
             LEFT JOIN produit pr ON pr.IdProduit = p.id_produit
             WHERE p.id_utilisateur = ? AND p.historique = ?
             ORDER BY p.date_ajout DESC',
            [$userId, 'hide']
        );

        $total = 0.0;
        foreach ($rows as $row) {
            $prix = $row['prix'] !== null ? (float) $row['prix'] : 0.0;
            $qty = (int) $row['quantite'];
            $total += $prix * $qty;
        }

        return $this->render('panier/historique.html.twig', [
            'items' => $rows,
            'total' => $total,
        ]);
    }

    #[Route('/panier/confirmer', name: 'panier_confirmer', methods: ['POST'])]
    public function confirmer(Request $request, Connection $connection): Response
    {
        $userId = 6;
        $token = $request->request->get('_token');
        if (!$this->isCsrfTokenValid('panier_confirmer', $token)) {
            return new RedirectResponse($this->generateUrl('panier_page'));
        }

        $userRow = $connection->fetchAssociative(
            'SELECT adresse, telephone FROM utilisateur WHERE id_utilisateur = ?',
            [$userId]
        );
        $localisation = $userRow['adresse'] ?? null;
        $telephone = $userRow['telephone'] ?? null;

        $items = $connection->fetchAllAssociative(
            'SELECT id_panier FROM panier
             WHERE id_utilisateur = ? AND (historique IS NULL OR historique = ?)
             ORDER BY date_ajout DESC',
            [$userId, 'show']
        );

        if (!$items) {
            $this->addFlash('success', 'Votre panier est vide.');
            return new RedirectResponse($this->generateUrl('panier_page'));
        }

        $now = (new \DateTime())->format('Y-m-d H:i:s');
        foreach ($items as $item) {
            $connection->insert('commande', [
                'id_panier' => $item['id_panier'],
                'id_utilisateur' => $userId,
                'id_livreur' => null,
                'date_commande' => $now,
                'etat' => 'cherche_livraison',
                'date_livraison' => null,
                'localisation' => $localisation,
                'telephone_client' => $telephone,
                'date_confirmation_livreur' => null,
            ]);
        }

        $connection->executeStatement(
            'UPDATE panier SET historique = ? WHERE id_utilisateur = ? AND (historique IS NULL OR historique = ?)',
            ['hide', $userId, 'show']
        );

        $this->addFlash('success', 'Commande enregistrÃ©e avec succÃ¨s.');
        return new RedirectResponse($this->generateUrl('panier_historique'));
    }
    #[Route('/panier/{id}/delete', name: 'panier_delete', methods: ['POST'])]
    public function delete(int $id, Request $request, Connection $connection): Response
    {
        $userId = 6;
        $token = $request->request->get('_token');
        if (!$this->isCsrfTokenValid('delete_panier_' . $id, $token)) {
            return new RedirectResponse($this->generateUrl('panier_page'));
        }

        $connection->delete('panier', [
            'id_panier' => $id,
            'id_utilisateur' => $userId,
        ]);

        $referer = $request->headers->get('referer');
        return new RedirectResponse($referer ?: $this->generateUrl('panier_page'));
    }

    // Export, historique and index removed.
}
