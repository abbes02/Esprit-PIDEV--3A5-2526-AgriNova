<?php

namespace App\Controller;

use App\Service\UserContext;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/users')]
final class UserSwitchController extends AbstractController
{
    #[Route('/switch', name: 'app_user_switch_index', methods: ['GET'])]
    public function index(Request $request, UserContext $userContext): Response
    {
        return $this->render('user/switch.html.twig', [
            'users' => $userContext->getAllUsers(),
            'current_user' => $userContext->getCurrentUser(),
            'redirect_to' => (string) $request->query->get('redirect_to', ''),
        ]);
    }

    #[Route('/switch/{id}', name: 'app_user_switch_apply', methods: ['POST'])]
    public function apply(int $id, Request $request, UserContext $userContext): Response
    {
        if (!$this->isCsrfTokenValid('switch_user_' . $id, (string) $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Invalid CSRF token.');
        }

        if (!$userContext->switchToUser($id)) {
            $this->addFlash('error', 'Utilisateur invalide.');

            return $this->redirectToRoute('app_user_switch_index');
        }

        $redirectTo = trim((string) $request->request->get('redirect_to', ''));
        $redirectToWithContext = $this->appendUserContextToRelativeUrl($redirectTo, $id);
        if ($redirectToWithContext !== null) {
            return $this->redirect($redirectToWithContext);
        }

        $this->addFlash('success', 'Contexte utilisateur mis a jour.');

        return $this->redirectToRoute('app_materiel_index', ['user_id' => $id]);
    }

    private function appendUserContextToRelativeUrl(string $redirectTo, int $userId): ?string
    {
        if ($redirectTo === '') {
            return null;
        }

        $parts = parse_url($redirectTo);
        if (!is_array($parts) || isset($parts['scheme']) || isset($parts['host'])) {
            return null;
        }

        $path = (string) ($parts['path'] ?? '');
        if ($path === '' || !str_starts_with($path, '/')) {
            return null;
        }

        $queryParams = [];
        parse_str((string) ($parts['query'] ?? ''), $queryParams);
        $queryParams['user_id'] = $userId;

        $finalUrl = $path;
        $queryString = http_build_query($queryParams);
        if ($queryString !== '') {
            $finalUrl .= '?' . $queryString;
        }

        if (isset($parts['fragment']) && $parts['fragment'] !== '') {
            $finalUrl .= '#' . $parts['fragment'];
        }

        return $finalUrl;
    }
}
