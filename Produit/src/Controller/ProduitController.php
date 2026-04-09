<?php

namespace App\Controller;

use App\Entity\Produit;
use App\Entity\ProduitAgricole;
use App\Entity\ProduitIOT;
use App\Form\ProduitAgricoleType;
use App\Form\ProduitIOTType;
use App\Repository\ProduitRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/produit')]
class ProduitController extends AbstractController
{

    #[Route('/check-ref', name: 'produit_check_ref', methods: ['GET'])]
    public function checkRef(Request $request, ProduitRepository $produitRepository): JsonResponse
    {
        $ref = trim((string) $request->query->get('ref', ''));
        if ($ref === '') {
            return new JsonResponse(['available' => false]);
        }

        $exists = $produitRepository->findOneBy(['ref' => $ref]) !== null;
        return new JsonResponse(['available' => !$exists]);
    }

    #[Route('/{id}', name: 'produit_show', methods: ['GET'])]
    public function show(Produit $produit): Response
    {
        return $this->render('produit/show.html.twig', [
            'produit' => $produit,
        ]);
    }

    #[Route('/{id}/edit', name: 'produit_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Produit $produit, EntityManagerInterface $entityManager): Response
    {
        $formType = $this->getFormTypeForProduit($produit);
        $form = $this->createForm($formType, $produit);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            if ($produit instanceof ProduitAgricole) {
                return $this->redirectToRoute('profile_produit_agricole');
            }

            return $this->redirectToRoute('produit_index');
        }

        if ($produit instanceof ProduitAgricole) {
            return $this->render('produit/edit_agricole.html.twig', [
                'produit' => $produit,
                'form' => $form,
            ]);
        }

        return $this->render('produit/edit.html.twig', [
            'produit' => $produit,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'produit_delete', methods: ['POST'])]
    public function delete(Request $request, Produit $produit, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $produit->getId(), $request->request->get('_token'))) {
            $entityManager->remove($produit);
            $entityManager->flush();
        }

        if ($produit instanceof ProduitAgricole) {
            return $this->redirectToRoute('profile_produit_agricole');
        }

        return $this->redirectToRoute('produit_index');
    }

    private function getFormTypeForProduit(Produit $produit): string
    {
        if ($produit instanceof ProduitAgricole) {
            return ProduitAgricoleType::class;
        }

        if ($produit instanceof ProduitIOT) {
            return ProduitIOTType::class;
        }

        throw $this->createNotFoundException('Type de produit invalide.');
    }
}
