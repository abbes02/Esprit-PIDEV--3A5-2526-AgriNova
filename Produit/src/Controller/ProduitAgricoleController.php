<?php

namespace App\Controller;

use App\Entity\ProduitAgricole;
use App\Repository\ProduitRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;

class ProduitAgricoleController extends AbstractController
{
    #[Route('/liste-produit-agricole', name: 'liste_produit_agricole', methods: ['GET'])]
    public function listeProduitAgricole(EntityManagerInterface $entityManager, Request $request): Response
    {
        $search = trim((string) $request->query->get('q', ''));
        $sort = (string) $request->query->get('sort', 'recent');

        $qb = $entityManager->getRepository(ProduitAgricole::class)->createQueryBuilder('p');
        if ($search !== '') {
            $qb
                ->andWhere('p.nom LIKE :q OR p.ref LIKE :q OR p.categorieAgricole LIKE :q')
                ->setParameter('q', '%' . $search . '%');
        }

        switch ($sort) {
            case 'price_asc':
                $qb->orderBy('p.prix', 'ASC');
                break;
            case 'price_desc':
                $qb->orderBy('p.prix', 'DESC');
                break;
            case 'name_asc':
                $qb->orderBy('p.nom', 'ASC');
                break;
            case 'name_desc':
                $qb->orderBy('p.nom', 'DESC');
                break;
            default:
                $qb->orderBy('p.dateAjout', 'DESC');
                break;
        }

        $produits = $qb->getQuery()->getResult();

        return $this->render('produit_agricole/liste_produit_agricole.html.twig', [
            'produits' => $produits,
            'search' => $search,
            'sort' => $sort,
        ]);
    }

    #[Route('/profile/produits-agricoles', name: 'profile_produit_agricole', methods: ['GET'])]
    public function profile(EntityManagerInterface $entityManager): Response
    {
        $userId = 6;
        $produits = $entityManager->getRepository(ProduitAgricole::class)->findBy([
            'proprietaire' => $userId,
        ]);

        return $this->render('produit_agricole/profile.html.twig', [
            'produits' => $produits,
        ]);
    }

    #[Route('/produits-agricoles/ajouter', name: 'produit_agricole_ajouter', methods: ['GET', 'POST'])]
    public function ajouter(Request $request, EntityManagerInterface $entityManager, ProduitRepository $produitRepository): Response
    {
        $produit = new ProduitAgricole();
        $form = $this->createForm(\App\Form\ProduitAgricoleType::class, $produit);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $existing = $produitRepository->findOneBy(['ref' => $produit->getRef()]);
            if ($existing) {
                $this->addFlash('success', 'La rÃ©fÃ©rence est dÃ©jÃ  utilisÃ©e.');
                return $this->redirectToRoute('produit_agricole_ajouter');
            }
            $produit->setProprietaire(6);
            $produit->setDateAjout(new \DateTime());
            $entityManager->persist($produit);
            $entityManager->flush();

            $this->addFlash('success', 'Produit agricole ajoutÃ©.');
            return $this->redirectToRoute('liste_produit_agricole');
        }

        return $this->render('produit_agricole/ajouter.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/profile/commandes', name: 'profile_commande', methods: ['GET'])]
    public function commandes(EntityManagerInterface $entityManager): Response
    {
        $userId = 6;
        $conn = $entityManager->getConnection();
        $commandes = $conn->fetchAllAssociative(
            'SELECT id_commande, id_panier, id_utilisateur, id_livreur, date_commande, etat, date_livraison, localisation, telephone_client, date_confirmation_livreur
             FROM commande
             WHERE id_utilisateur = :userId
             ORDER BY date_commande DESC',
            ['userId' => $userId]
        );

        $panierIds = array_values(array_filter(array_map(
            static fn (array $row): ?int => isset($row['id_panier']) ? (int) $row['id_panier'] : null,
            $commandes
        )));
        $panierItems = [];
        if ($panierIds) {
            $placeholders = implode(',', array_fill(0, count($panierIds), '?'));
            $rows = $conn->fetchAllAssociative(
                "SELECT p.id_panier, p.id_produit, p.quantite, pr.Nom AS nom, pr.Prix AS prix, pr.Image AS image, pr.TypeProduit AS type_produit
                 FROM panier p
                 INNER JOIN produit pr ON pr.IdProduit = p.id_produit
                 WHERE p.id_panier IN ($placeholders)
                 ORDER BY p.id_panier ASC",
                $panierIds
            );
            foreach ($rows as $row) {
                $pid = (int) $row['id_panier'];
                if (!isset($panierItems[$pid])) {
                    $panierItems[$pid] = [];
                }
                $panierItems[$pid][] = $row;
            }
        }

        return $this->render('profile/commande.html.twig', [
            'commandes' => $commandes,
            'panier_items' => $panierItems,
        ]);
    }

    #[Route('/produits-agricoles/image/{id}', name: 'produit_agricole_image', methods: ['GET'])]
    public function image(ProduitAgricole $produit): Response
    {
        $path = $produit->getImage();
        if ($path === null || $path === '') {
            throw $this->createNotFoundException('Image introuvable.');
        }

        $realPath = realpath($path);
        if ($realPath === false || !is_file($realPath)) {
            throw $this->createNotFoundException('Image introuvable.');
        }

        $allowedRoot = realpath('C:\\Users\\saidi\\');
        if ($allowedRoot === false || strncmp($realPath, $allowedRoot, strlen($allowedRoot)) !== 0) {
            throw $this->createNotFoundException('Chemin image non autorise.');
        }

        $response = new BinaryFileResponse($realPath);
        $mime = mime_content_type($realPath);
        if ($mime !== false) {
            $response->headers->set('Content-Type', $mime);
        }
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_INLINE);

        return $response;
    }

    #[Route('/produits-agricoles/{id}/panier', name: 'produit_agricole_add_panier', methods: ['POST'])]
    public function addToPanier(ProduitAgricole $produit, Request $request, EntityManagerInterface $entityManager): Response
    {
        $quantite = (int) $request->request->get('quantite', 1);
        if ($quantite < 1) {
            $quantite = 1;
        }

        $userId = 6;
        $entityManager->getConnection()->insert('panier', [
            'id_utilisateur' => $userId,
            'id_produit' => $produit->getId(),
            'quantite' => $quantite,
            'date_ajout' => (new \DateTime())->format('Y-m-d H:i:s'),
            'historique' => 'show',
        ]);

        $this->addFlash('success', 'Produit ajoutÃ© au panier.');

        $referer = $request->headers->get('referer');
        return new RedirectResponse($referer ?: $this->generateUrl('produit_agricole_list'));
    }
}
