<?php

namespace App\Controller;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Twig\Environment;

final class RegistrationController
{
    private const ALLOWED_ROLES = ['CLIENT', 'LIVREUR', 'AGRICULTEUR'];

    #[Route('/register', name: 'app_register', methods: ['GET', 'POST'])]
    public function __invoke(Request $request, Connection $connection, Environment $twig): Response
    {
        $session = $request->getSession();

        if ($session->get('auth_user')) {
            return new RedirectResponse('/dashboard');
        }

        $values = [
            'nom' => trim((string) $request->request->get('nom', '')),
            'prenom' => trim((string) $request->request->get('prenom', '')),
            'email' => trim((string) $request->request->get('email', '')),
            'telephone' => trim((string) $request->request->get('telephone', '')),
            'adresse' => trim((string) $request->request->get('adresse', '')),
            'role' => strtoupper(trim((string) $request->request->get('role', 'CLIENT'))),
        ];

        $errorMessage = null;

        if ($request->isMethod('POST')) {
            $password = (string) $request->request->get('password', '');
            $passwordConfirmation = (string) $request->request->get('password_confirmation', '');

            if ($values['nom'] === '' || $values['prenom'] === '' || $values['email'] === '' || $values['adresse'] === '' || $password === '') {
                $errorMessage = 'Tous les champs obligatoires doivent etre renseignes.';
            } elseif (!filter_var($values['email'], FILTER_VALIDATE_EMAIL)) {
                $errorMessage = 'Adresse email invalide.';
            } elseif (!in_array($values['role'], self::ALLOWED_ROLES, true)) {
                $errorMessage = 'Role invalide.';
            } elseif ($password !== $passwordConfirmation) {
                $errorMessage = 'La confirmation du mot de passe ne correspond pas.';
            } elseif (mb_strlen($password) < 6) {
                $errorMessage = 'Le mot de passe doit contenir au moins 6 caracteres.';
            } else {
                try {
                    $existingUser = $connection->createQueryBuilder()
                        ->select('u.id_utilisateur')
                        ->from('utilisateur', 'u')
                        ->where('LOWER(u.email) = LOWER(:email)')
                        ->setParameter('email', $values['email'])
                        ->setMaxResults(1)
                        ->fetchOne();

                    if ($existingUser !== false) {
                        $errorMessage = 'Cet email existe deja.';
                    } else {
                        $connection->insert('utilisateur', [
                            'nom' => $values['nom'],
                            'prenom' => $values['prenom'],
                            'email' => $values['email'],
                            'mot_de_passe' => password_hash($password, PASSWORD_DEFAULT),
                            'telephone' => $values['telephone'] !== '' ? $values['telephone'] : null,
                            'adresse' => $values['adresse'],
                            'role' => $values['role'],
                            'statut' => 'ACTIF',
                            'date_creation' => (new \DateTimeImmutable())->format('Y-m-d H:i:s'),
                            'points_cadeaux' => 0,
                        ]);

                        return new RedirectResponse('/login?registered=1');
                    }
                } catch (Exception) {
                    $errorMessage = 'Impossible de creer le compte pour le moment.';
                }
            }
        }

        return new Response($twig->render('registration/index.html.twig', [
            'values' => $values,
            'error_message' => $errorMessage,
            'allowed_roles' => self::ALLOWED_ROLES,
        ]));
    }
}
