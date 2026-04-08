<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class DashboardController
{
    #[Route('/dashboard', name: 'app_dashboard', methods: ['GET'])]
    public function __invoke(Request $request): Response
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

        return match ($userRole) {
            'ADMIN' => new RedirectResponse('/admin/users'),
            'CLIENT' => new RedirectResponse('/client'),
            'LIVREUR' => new RedirectResponse('/livreur'),
            'AGRICULTEUR' => new RedirectResponse('/agriculteur/home'),
            default => new RedirectResponse('/login'),
        };
    }
}
