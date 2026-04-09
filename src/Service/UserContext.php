<?php

namespace App\Service;

use App\Entity\Utilisateur;
use App\Repository\UtilisateurRepository;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class UserContext
{
    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly UtilisateurRepository $utilisateurRepository,
    ) {
    }

    public function getCurrentUserId(): ?int
    {
        $request = $this->requestStack->getCurrentRequest();
        if ($request === null) {
            return null;
        }

        if (!$request->hasSession()) {
            return null;
        }

        $authUser = $request->getSession()->get('auth_user');
        if (!is_array($authUser)) {
            return null;
        }

        $rawUserId = $authUser['id'] ?? null;
        $userId = $this->normalizeUserId($rawUserId);

        if ($userId === null) {
            return null;
        }

        return $this->utilisateurRepository->find($userId) instanceof Utilisateur ? $userId : null;
    }

    public function getCurrentUser(): ?Utilisateur
    {
        $userId = $this->getCurrentUserId();
        if ($userId === null) {
            return null;
        }

        return $this->utilisateurRepository->find($userId);
    }

    public function requireCurrentUser(): Utilisateur
    {
        $user = $this->getCurrentUser();
        if (!$user instanceof Utilisateur) {
            throw new AccessDeniedHttpException('Authenticated utilisateur session is required.');
        }

        return $user;
    }

    private function normalizeUserId(mixed $rawUserId): ?int
    {
        if ($rawUserId === null || $rawUserId === '') {
            return null;
        }

        if (!ctype_digit((string) $rawUserId)) {
            return null;
        }

        $userId = (int) $rawUserId;

        return $userId > 0 ? $userId : null;
    }
}
