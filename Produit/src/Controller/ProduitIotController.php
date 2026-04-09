<?php

namespace App\Controller;

use App\Entity\ProduitIOT;
use App\Form\ProduitIOTType;
use App\Repository\ProduitRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;

class ProduitIotController extends AbstractController
{
    #[Route('/produits-iot', name: 'produit_iot_list', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $produits = $entityManager->getRepository(ProduitIOT::class)->findAll();

        return $this->render('produit_iot/index.html.twig', [
            'produits' => $produits,
        ]);
    }

    #[Route('/liste-produit-iot', name: 'liste_produit_iot', methods: ['GET'])]
    public function listeProduitIot(EntityManagerInterface $entityManager): Response
    {
        $produits = $entityManager->getRepository(ProduitIOT::class)->findAll();

        return $this->render('produit_iot/ListeProduitIOT.html.twig', [
            'produits' => $produits,
        ]);
    }

    #[Route('/produits-iot/afficher', name: 'produit_iot_afficher', methods: ['GET'])]
    public function afficher(EntityManagerInterface $entityManager): Response
    {
        $produits = $entityManager->getRepository(ProduitIOT::class)->findAll();

        return $this->render('produit_iot/afficher.html.twig', [
            'produits' => $produits,
        ]);
    }

    #[Route('/backoffice/produits-iot', name: 'backoffice_produit_iot_afficher', methods: ['GET'])]
    public function backofficeAfficher(EntityManagerInterface $entityManager): Response
    {
        $produits = $entityManager->getRepository(ProduitIOT::class)->findAll();
        $categories = [];
        foreach ($produits as $produit) {
            $categorie = $produit->getCategorie();
            if ($categorie !== null && $categorie !== '') {
                $categories[] = $categorie;
            }
        }
        $categories = array_values(array_unique($categories));

        return $this->render('backoffice/afficher_produit_iot.html.twig', [
            'produits' => $produits,
            'categorie_count' => count($categories),
        ]);
    }

    #[Route('/backoffice/produits-iot/ajouter', name: 'backoffice_produit_iot_ajouter', methods: ['GET', 'POST'])]
    public function backofficeAjouter(Request $request, EntityManagerInterface $entityManager, ProduitRepository $produitRepository): Response
    {
        $produit = new ProduitIOT();
        $form = $this->createForm(ProduitIOTType::class, $produit);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $existing = $produitRepository->findOneBy(['ref' => $produit->getRef()]);
            if ($existing) {
                $this->addFlash('success', 'La reference est deja utilisee.');
                return $this->redirectToRoute('backoffice_produit_iot_ajouter');
            }
            $produit->setProprietaire(6);
            $produit->setDateAjout(new \DateTime());
            $entityManager->persist($produit);
            $entityManager->flush();

            $this->addFlash('success', 'Produit IOT ajoute.');
            return $this->redirectToRoute('backoffice_produit_iot_afficher');
        }

        return $this->render('backoffice/ajouter_produit_iot.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/produits-iot/image/{id}', name: 'produit_iot_image', methods: ['GET'])]
    public function image(ProduitIOT $produit): Response
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

    #[Route('/produits-iot/{id}/panier', name: 'produit_iot_add_panier', methods: ['POST'])]
    public function addToPanier(ProduitIOT $produit, Request $request, EntityManagerInterface $entityManager): Response
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

        $referer = $request->headers->get('referer');
        return new RedirectResponse($referer ?: $this->generateUrl('produit_iot_list'));
    }
}
