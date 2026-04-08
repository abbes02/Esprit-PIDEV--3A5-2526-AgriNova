<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use App\Form\UtilisateurType;
use App\Repository\UtilisateurRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register', methods: ['GET', 'POST'])]
    public function __invoke(
        Request $request,
        EntityManagerInterface $entityManager,
        UtilisateurRepository $utilisateurRepository
    ): Response {
        $session = $request->getSession();

        if ($session->get('auth_user')) {
            return new RedirectResponse('/dashboard');
        }

        $utilisateur = new Utilisateur();
        $utilisateur->setRole('CLIENT');
        $utilisateur->setStatut('ACTIF');
        $utilisateur->setPointsCadeaux(0);

        $form = $this->createForm(UtilisateurType::class, $utilisateur);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $existingUser = $utilisateurRepository->findOneBy([
                'email' => $utilisateur->getEmail(),
            ]);

            if ($existingUser !== null) {
                $form->get('email')->addError(
                    new \Symfony\Component\Form\FormError('Cet email existe deja.')
                );
            } else {
                $utilisateur->setMotDePasse(
                    password_hash((string) $utilisateur->getMotDePasse(), PASSWORD_DEFAULT)
                );
                $utilisateur->setDateCreation(new \DateTimeImmutable());

                $entityManager->persist($utilisateur);
                $entityManager->flush();

                return $this->redirect('/login?registered=1');
            }
        }

        return $this->render('registration/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
