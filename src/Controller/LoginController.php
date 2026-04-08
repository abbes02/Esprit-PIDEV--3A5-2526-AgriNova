<?php

namespace App\Controller;

use App\Security\DatabaseUserAuthenticator;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Twig\Environment;

final class LoginController
{
    #[Route('/login', name: 'app_login', methods: ['GET', 'POST'])]
    public function __invoke(
        Request $request,
        DatabaseUserAuthenticator $authenticator,
        Environment $twig,
    ): Response
    {
        $session = $request->getSession();

        if ($session->get('auth_user')) {
            return new RedirectResponse('/dashboard');
        }

        $errorMessage = null;
        $submittedEmail = '';
        $successMessage = $request->query->getBoolean('registered')
            ? 'Compte cree avec succes. Tu peux maintenant te connecter.'
            : null;

        if ($request->isMethod('POST')) {
            $submittedEmail = trim((string) $request->request->get('email', ''));
            $submittedPassword = (string) $request->request->get('password', '');
            $result = $authenticator->authenticate($submittedEmail, $submittedPassword);

            if ($result->success && $result->user !== null) {
                $session->set('auth_user', $result->user->toSession());

                return new RedirectResponse('/dashboard');
            }

            $errorMessage = $result->error ?? 'Email ou mot de passe invalide.';
        }

        return new Response($twig->render('login/index.html.twig', [
            'submitted_email' => $submittedEmail,
            'error_message' => $errorMessage,
            'success_message' => $successMessage,
        ]));
    }

    #[Route('/logout', name: 'app_logout', methods: ['POST'])]
    public function logout(Request $request): RedirectResponse
    {
        $request->getSession()->invalidate();

        return new RedirectResponse('/login');
    }
}
