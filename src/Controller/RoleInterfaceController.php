<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Twig\Environment;

final class RoleInterfaceController
{
    #[Route('/client', name: 'app_client_interface', methods: ['GET'])]
    public function client(Request $request, Environment $twig): Response
    {
        return $this->renderRolePage($request, 'CLIENT', 'role/client.html.twig', 'client', $twig);
    }

    #[Route('/livreur', name: 'app_livreur_interface', methods: ['GET'])]
    public function livreur(Request $request, Environment $twig): Response
    {
        return $this->renderRolePage($request, 'LIVREUR', 'role/livreur.html.twig', 'livreur', $twig);
    }

    #[Route('/agriculteur', name: 'app_agriculteur_interface', methods: ['GET'])]
    public function agriculteur(Request $request): Response
    {
        $user = $request->getSession()->get('auth_user');

        if (!is_array($user)) {
            return new RedirectResponse('/login');
        }

        $userRole = strtoupper((string) ($user['role'] ?? ''));

        if ($userRole !== 'AGRICULTEUR') {
            return new RedirectResponse('/dashboard');
        }

        return new RedirectResponse('/agriculteur/home');
    }

    private function renderRolePage(
        Request $request,
        string $expectedRole,
        string $template,
        string $roleLabel,
        Environment $twig,
    ): Response
    {
        $user = $request->getSession()->get('auth_user');

        if (!is_array($user)) {
            return new RedirectResponse('/login');
        }

        $userEmail = (string) ($user['email'] ?? '');
        $userDisplayName = (string) ($user['display_name'] ?? $userEmail);
        $userRole = strtoupper((string) ($user['role'] ?? ''));

        if ($userEmail === '') {
            return new RedirectResponse('/login');
        }

        if ($userRole !== $expectedRole) {
            return new RedirectResponse('/dashboard');
        }

        return new Response($twig->render($template, [
            'role' => strtolower($userRole),
            'role_label' => $roleLabel,
            'user_display_name' => $userDisplayName,
            'user_email' => $userEmail,
        ]));
    }
}
