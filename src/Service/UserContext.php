<?php

namespace App\Service;

use App\Entity\Utilisateur;
use App\Repository\UtilisateurRepository;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class UserContext
{
    private const SESSION_USER_KEY = 'agrinova_active_user_id';

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

        $queryUserId = $this->normalizeUserId($request->query->get('user_id', $request->headers->get('X-User-Id')));
        if ($queryUserId !== null && $this->utilisateurRepository->find($queryUserId) instanceof Utilisateur) {
            $this->storeInSession($queryUserId);

            return $queryUserId;
        }

        $sessionUserId = null;
        if ($request->hasSession()) {
            $sessionUserId = $this->normalizeUserId($request->getSession()->get(self::SESSION_USER_KEY));
        }

        if ($sessionUserId !== null && $this->utilisateurRepository->find($sessionUserId) instanceof Utilisateur) {
            return $sessionUserId;
        }

        $defaultUser = $this->utilisateurRepository->findOneBy([], ['id' => 'ASC']);
        if (!$defaultUser instanceof Utilisateur || $defaultUser->getId() === null) {
            return null;
        }

        $defaultUserId = (int) $defaultUser->getId();
        $this->storeInSession($defaultUserId);

        return $defaultUserId;
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
            throw new BadRequestHttpException('No utilisateur record is available to build user context.');
        }

        return $user;
    }

    public function switchToUser(int $userId): bool
    {
        if ($userId <= 0) {
            return false;
        }

        if (!$this->utilisateurRepository->find($userId) instanceof Utilisateur) {
            return false;
        }

        $this->storeInSession($userId);

        return true;
    }

    /**
     * @return Utilisateur[]
     */
    public function getAllUsers(): array
    {
        return $this->utilisateurRepository->findBy([], ['id' => 'ASC']);
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

    private function storeInSession(int $userId): void
    {
        $request = $this->requestStack->getCurrentRequest();
        if ($request === null || !$request->hasSession()) {
            return;
        }

        $request->getSession()->set(self::SESSION_USER_KEY, $userId);
    }
}
