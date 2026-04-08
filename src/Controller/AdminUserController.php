<?php

namespace App\Controller;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Twig\Environment;

final class AdminUserController
{
    private const MANAGEABLE_ROLES = ['ADMIN', 'CLIENT', 'LIVREUR', 'AGRICULTEUR'];

    #[Route('/admin/users', name: 'app_admin_users', methods: ['GET'])]
    public function index(Request $request, Connection $connection, Environment $twig): Response
    {
        $sessionUser = $this->requireAdmin($request);
        if ($sessionUser instanceof RedirectResponse) {
            return $sessionUser;
        }

        $search = trim((string) $request->query->get('q', ''));
        $alphabetical = strtoupper((string) $request->query->get('alpha', 'ASC'));
        $alphabetical = in_array($alphabetical, ['ASC', 'DESC'], true) ? $alphabetical : 'ASC';

        try {
            $queryBuilder = $connection->createQueryBuilder()
                ->select('u.id_utilisateur', 'u.nom', 'u.prenom', 'u.email', 'u.role', 'u.statut', 'u.telephone', 'u.adresse', 'u.date_creation')
                ->from('utilisateur', 'u');

            if ($search !== '') {
                $queryBuilder
                    ->andWhere('u.nom LIKE :search OR u.prenom LIKE :search OR u.email LIKE :search OR u.telephone LIKE :search')
                    ->setParameter('search', '%'.$search.'%');
            }

            $users = $queryBuilder
                ->orderBy('u.nom', $alphabetical)
                ->addOrderBy('u.prenom', $alphabetical)
                ->fetchAllAssociative();
        } catch (Exception) {
            $request->getSession()->getFlashBag()->add('admin_error', 'Impossible de charger la liste des utilisateurs.');

            $users = [];
        }

        return new Response($twig->render('admin/users.html.twig', [
            'alerts' => $this->getAlerts($request),
            'users' => $this->normalizeUsers($users, (string) $sessionUser['id']),
            'role_stats' => $this->buildRoleStats($users),
            'admin_email' => (string) $sessionUser['email'],
            'admin_name' => (string) ($sessionUser['display_name'] ?? $sessionUser['email']),
            'admin_role' => strtoupper((string) ($sessionUser['role'] ?? 'ADMIN')),
            'admin_profile' => $this->extractAdminProfile(
                $users,
                (string) $sessionUser['id'],
                (string) ($sessionUser['display_name'] ?? $sessionUser['email']),
                (string) $sessionUser['email'],
                strtoupper((string) ($sessionUser['role'] ?? 'ADMIN'))
            ),
            'search' => $search,
            'alphabetical' => $alphabetical,
            'manageable_roles' => self::MANAGEABLE_ROLES,
        ]));
    }

    #[Route('/admin/profile/update', name: 'app_admin_profile_update', methods: ['POST'])]
    public function updateProfile(Request $request, Connection $connection): RedirectResponse
    {
        $sessionUser = $this->requireAdmin($request);
        if ($sessionUser instanceof RedirectResponse) {
            return $sessionUser;
        }

        $nom = trim((string) $request->request->get('nom', ''));
        $prenom = trim((string) $request->request->get('prenom', ''));
        $email = trim((string) $request->request->get('email', ''));
        $photo = $request->files->get('photo');

        if ($nom === '' || $prenom === '' || $email === '') {
            $request->getSession()->getFlashBag()->add('admin_error', 'Nom, prenom et email sont obligatoires.');

            return new RedirectResponse('/admin/users');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $request->getSession()->getFlashBag()->add('admin_error', 'Adresse email invalide.');

            return new RedirectResponse('/admin/users');
        }

        try {
            $existingUserId = $connection->createQueryBuilder()
                ->select('u.id_utilisateur')
                ->from('utilisateur', 'u')
                ->where('LOWER(u.email) = LOWER(:email)')
                ->andWhere('u.id_utilisateur <> :id')
                ->setParameter('email', $email)
                ->setParameter('id', (string) $sessionUser['id'])
                ->setMaxResults(1)
                ->fetchOne();

            if ($existingUserId !== false) {
                $request->getSession()->getFlashBag()->add('admin_error', 'Cet email est deja utilise.');

                return new RedirectResponse('/admin/users');
            }

            $connection->update('utilisateur', [
                'nom' => $nom,
                'prenom' => $prenom,
                'email' => $email,
            ], [
                'id_utilisateur' => (string) $sessionUser['id'],
            ]);
        } catch (\Throwable) {
            $request->getSession()->getFlashBag()->add('admin_error', 'Impossible de mettre a jour le profil administrateur.');

            return new RedirectResponse('/admin/users');
        }

        try {
            if ($photo instanceof UploadedFile) {
                if ($photo->getError() !== UPLOAD_ERR_OK) {
                    throw new \RuntimeException($this->mapUploadError($photo->getError()));
                }

                $this->storeAdminPhoto((string) $sessionUser['id'], $photo);
            }
        } catch (\Throwable $exception) {
            $request->getSession()->set('auth_user', [
                'id' => (string) $sessionUser['id'],
                'email' => $email,
                'display_name' => trim($nom.' '.$prenom),
                'role' => strtoupper((string) ($sessionUser['role'] ?? 'ADMIN')),
            ]);
            $request->getSession()->getFlashBag()->add('admin_error', sprintf('Profil mis a jour, mais la photo a echoue : %s', $exception->getMessage()));

            return new RedirectResponse('/admin/users');
        }

        $request->getSession()->set('auth_user', [
            'id' => (string) $sessionUser['id'],
            'email' => $email,
            'display_name' => trim($nom.' '.$prenom),
            'role' => strtoupper((string) ($sessionUser['role'] ?? 'ADMIN')),
        ]);

        $request->getSession()->getFlashBag()->add('admin_success', 'Profil mis a jour avec succes.');

        return new RedirectResponse('/admin/users');
    }

    #[Route('/admin/profile/password', name: 'app_admin_profile_password', methods: ['POST'])]
    public function updatePassword(Request $request, Connection $connection): RedirectResponse
    {
        $sessionUser = $this->requireAdmin($request);
        if ($sessionUser instanceof RedirectResponse) {
            return $sessionUser;
        }

        $currentPassword = (string) $request->request->get('current_password', '');
        $newPassword = (string) $request->request->get('new_password', '');
        $confirmPassword = (string) $request->request->get('confirm_password', '');

        if ($currentPassword === '' || $newPassword === '' || $confirmPassword === '') {
            $request->getSession()->getFlashBag()->add('admin_error', 'Tous les champs du mot de passe sont obligatoires.');

            return new RedirectResponse('/admin/users');
        }

        if ($newPassword !== $confirmPassword) {
            $request->getSession()->getFlashBag()->add('admin_error', 'La confirmation du nouveau mot de passe ne correspond pas.');

            return new RedirectResponse('/admin/users');
        }

        if (mb_strlen($newPassword) < 6) {
            $request->getSession()->getFlashBag()->add('admin_error', 'Le nouveau mot de passe doit contenir au moins 6 caracteres.');

            return new RedirectResponse('/admin/users');
        }

        try {
            $storedPassword = $connection->createQueryBuilder()
                ->select('u.mot_de_passe')
                ->from('utilisateur', 'u')
                ->where('u.id_utilisateur = :id')
                ->setParameter('id', (string) $sessionUser['id'])
                ->setMaxResults(1)
                ->fetchOne();

            if (!is_string($storedPassword) || $storedPassword === '') {
                $request->getSession()->getFlashBag()->add('admin_error', 'Mot de passe actuel introuvable.');

                return new RedirectResponse('/admin/users');
            }

            if (!$this->passwordMatches($currentPassword, $storedPassword)) {
                $request->getSession()->getFlashBag()->add('admin_error', 'Le mot de passe actuel est incorrect.');

                return new RedirectResponse('/admin/users');
            }

            $connection->update('utilisateur', [
                'mot_de_passe' => password_hash($newPassword, PASSWORD_DEFAULT),
            ], [
                'id_utilisateur' => (string) $sessionUser['id'],
            ]);

            $request->getSession()->getFlashBag()->add('admin_success', 'Mot de passe mis a jour avec succes.');
        } catch (\Throwable) {
            $request->getSession()->getFlashBag()->add('admin_error', 'Impossible de mettre a jour le mot de passe.');
        }

        return new RedirectResponse('/admin/users');
    }

    #[Route('/admin/profile/delete', name: 'app_admin_profile_delete', methods: ['POST'])]
    public function deleteOwnAccount(Request $request, Connection $connection): RedirectResponse
    {
        $sessionUser = $this->requireAdmin($request);
        if ($sessionUser instanceof RedirectResponse) {
            return $sessionUser;
        }

        try {
            $affectedRows = $connection->delete('utilisateur', ['id_utilisateur' => (string) $sessionUser['id']]);

            if ($affectedRows < 1) {
                $request->getSession()->getFlashBag()->add('admin_error', 'Compte administrateur introuvable.');

                return new RedirectResponse('/admin/users');
            }

            $request->getSession()->invalidate();

            return new RedirectResponse('/login');
        } catch (\Throwable) {
            $request->getSession()->getFlashBag()->add('admin_error', 'Impossible de supprimer le compte administrateur.');

            return new RedirectResponse('/admin/users');
        }
    }

    #[Route('/admin/users/{id}/role', name: 'app_admin_users_update_role', methods: ['POST'])]
    public function updateRole(string $id, Request $request, Connection $connection): RedirectResponse
    {
        $sessionUser = $this->requireAdmin($request);
        if ($sessionUser instanceof RedirectResponse) {
            return $sessionUser;
        }

        $role = strtoupper(trim((string) $request->request->get('role', '')));

        if (!in_array($role, self::MANAGEABLE_ROLES, true)) {
            $request->getSession()->getFlashBag()->add('admin_error', 'Role invalide.');

            return new RedirectResponse('/admin/users');
        }

        if ($id === (string) $sessionUser['id']) {
            $request->getSession()->getFlashBag()->add('admin_error', 'Tu ne peux pas modifier ton propre role depuis cette interface.');

            return new RedirectResponse('/admin/users');
        }

        try {
            $affectedRows = $connection->update('utilisateur', ['role' => $role], ['id_utilisateur' => $id]);

            if ($affectedRows < 1) {
                $request->getSession()->getFlashBag()->add('admin_error', 'Utilisateur introuvable.');
            } else {
                $request->getSession()->getFlashBag()->add('admin_success', 'Role mis a jour avec succes.');
            }
        } catch (\Throwable) {
            $request->getSession()->getFlashBag()->add('admin_error', 'Impossible de mettre a jour le role.');
        }

        return new RedirectResponse('/admin/users');
    }

    #[Route('/admin/users/{id}/delete', name: 'app_admin_users_delete', methods: ['POST'])]
    public function delete(string $id, Request $request, Connection $connection): RedirectResponse
    {
        $sessionUser = $this->requireAdmin($request);
        if ($sessionUser instanceof RedirectResponse) {
            return $sessionUser;
        }

        if ($id === (string) $sessionUser['id']) {
            $request->getSession()->getFlashBag()->add('admin_error', 'Tu ne peux pas supprimer ton propre compte.');

            return new RedirectResponse('/admin/users');
        }

        try {
            $affectedRows = $connection->delete('utilisateur', ['id_utilisateur' => $id]);

            if ($affectedRows < 1) {
                $request->getSession()->getFlashBag()->add('admin_error', 'Utilisateur introuvable.');
            } else {
                $request->getSession()->getFlashBag()->add('admin_success', 'Utilisateur supprime avec succes.');
            }
        } catch (\Throwable) {
            $request->getSession()->getFlashBag()->add('admin_error', 'Impossible de supprimer cet utilisateur.');
        }

        return new RedirectResponse('/admin/users');
    }

    /**
     * @return array{id: string, email: string, display_name: string, role: string}|RedirectResponse
     */
    private function requireAdmin(Request $request): array|RedirectResponse
    {
        $user = $request->getSession()->get('auth_user');

        if (!is_array($user) || (($user['email'] ?? '') === '')) {
            return new RedirectResponse('/login');
        }

        if (strtoupper((string) ($user['role'] ?? '')) !== 'ADMIN') {
            return new RedirectResponse('/dashboard');
        }

        return $user;
    }

    /**
     * @param list<array<string, mixed>> $users
     * @return list<array{id: string, display_name: string, is_current_user: bool, email: string, phone: string, address: string, created_at: string, status: string, role: string}>
     */
    private function normalizeUsers(array $users, string $currentUserId): array
    {
        if ($users === []) {
            return [];
        }

        $rows = [];

        foreach ($users as $user) {
            $userId = (string) ($user['id_utilisateur'] ?? '');
            $isCurrentUser = $userId === $currentUserId;
            $displayName = trim(sprintf('%s %s', (string) ($user['nom'] ?? ''), (string) ($user['prenom'] ?? '')));
            $displayName = $displayName !== '' ? $displayName : 'Sans nom';
            $role = strtoupper((string) ($user['role'] ?? ''));
            $status = (string) ($user['statut'] ?? '');
            $phone = (string) ($user['telephone'] ?? '');
            $address = (string) ($user['adresse'] ?? '');
            $email = (string) ($user['email'] ?? '');
            $createdAt = $this->formatDate((string) ($user['date_creation'] ?? ''));

            $rows[] = [
                'id' => $userId,
                'display_name' => $displayName,
                'is_current_user' => $isCurrentUser,
                'email' => $email,
                'phone' => $phone !== '' ? $phone : '-',
                'address' => $address !== '' ? $address : '-',
                'created_at' => $createdAt,
                'status' => $status !== '' ? $status : '-',
                'role' => $role,
            ];
        }

        return $rows;
    }

    /**
     * @return array{success: list<string>, error: list<string>}
     */
    private function getAlerts(Request $request): array
    {
        $flashBag = $request->getSession()->getFlashBag();

        return [
            'success' => array_map(static fn (mixed $message): string => (string) $message, $flashBag->get('admin_success', [])),
            'error' => array_map(static fn (mixed $message): string => (string) $message, $flashBag->get('admin_error', [])),
        ];
    }

    private function formatDate(string $value): string
    {
        if ($value === '') {
            return '-';
        }

        try {
            return (new \DateTimeImmutable($value))->format('d/m/Y H:i');
        } catch (\Throwable) {
            return $value;
        }
    }

    /**
     * @param list<array<string, mixed>> $users
     * @return array{name: string, first_name: string, last_name: string, email: string, role: string, phone: string, address: string, status: string, created_at: string, photo_url: string}
     */
    private function extractAdminProfile(array $users, string $currentUserId, string $fallbackName, string $fallbackEmail, string $fallbackRole): array
    {
        $photos = $this->loadProfilePhotos();

        foreach ($users as $user) {
            if ((string) ($user['id_utilisateur'] ?? '') !== $currentUserId) {
                continue;
            }

            $firstName = (string) ($user['nom'] ?? '');
            $lastName = (string) ($user['prenom'] ?? '');
            $name = trim(sprintf('%s %s', $firstName, $lastName));

            return [
                'name' => $name !== '' ? $name : $fallbackName,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'email' => (string) ($user['email'] ?? $fallbackEmail),
                'role' => strtoupper((string) ($user['role'] ?? $fallbackRole)),
                'phone' => (string) (($user['telephone'] ?? '') !== '' ? $user['telephone'] : '-'),
                'address' => (string) (($user['adresse'] ?? '') !== '' ? $user['adresse'] : '-'),
                'status' => (string) (($user['statut'] ?? '') !== '' ? $user['statut'] : '-'),
                'created_at' => $this->formatDate((string) ($user['date_creation'] ?? '')),
                'photo_url' => (string) ($photos[$currentUserId] ?? '/logo.png'),
            ];
        }

        return [
            'name' => $fallbackName,
            'first_name' => $fallbackName,
            'last_name' => '',
            'email' => $fallbackEmail,
            'role' => $fallbackRole,
            'phone' => '-',
            'address' => '-',
            'status' => '-',
            'created_at' => '-',
            'photo_url' => (string) ($photos[$currentUserId] ?? '/logo.png'),
        ];
    }

    private function passwordMatches(string $plainPassword, string $storedPassword): bool
    {
        $passwordInfo = password_get_info($storedPassword);

        if (($passwordInfo['algo'] ?? null) !== null) {
            return password_verify($plainPassword, $storedPassword);
        }

        return hash_equals($storedPassword, $plainPassword);
    }

    /**
     * @param list<array<string, mixed>> $users
     * @return array{CLIENT: int, LIVREUR: int, AGRICULTEUR: int}
     */
    private function buildRoleStats(array $users): array
    {
        $stats = [
            'CLIENT' => 0,
            'LIVREUR' => 0,
            'AGRICULTEUR' => 0,
        ];

        foreach ($users as $user) {
            $role = strtoupper((string) ($user['role'] ?? ''));
            if (array_key_exists($role, $stats)) {
                $stats[$role] += 1;
            }
        }

        return $stats;
    }

    /**
     * @return array<string, string>
     */
    private function loadProfilePhotos(): array
    {
        $path = $this->getProfilePhotosPath();

        if (!is_file($path)) {
            return [];
        }

        $decoded = json_decode((string) file_get_contents($path), true);

        return is_array($decoded) ? array_filter($decoded, 'is_string') : [];
    }

    /**
     * @param array<string, string> $photos
     */
    private function saveProfilePhotos(array $photos): void
    {
        $path = $this->getProfilePhotosPath();
        $directory = dirname($path);

        if (!is_dir($directory)) {
            mkdir($directory, 0777, true);
        }

        file_put_contents($path, json_encode($photos, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }

    private function storeAdminPhoto(string $userId, UploadedFile $photo): void
    {
        $allowedMimeTypes = ['image/jpeg', 'image/jpg', 'image/pjpeg', 'image/png', 'image/webp', 'image/gif'];
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
        $mimeType = '';
        $originalExtension = strtolower((string) $photo->getClientOriginalExtension());

        if (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            if ($finfo !== false) {
                $detectedMimeType = finfo_file($finfo, $photo->getPathname());
                if (is_string($detectedMimeType)) {
                    $mimeType = strtolower($detectedMimeType);
                }
                finfo_close($finfo);
            }
        }

        if (
            !in_array($mimeType, $allowedMimeTypes, true)
            && !in_array($originalExtension, $allowedExtensions, true)
        ) {
            throw new \RuntimeException('Format de photo invalide.');
        }

        $extension = $originalExtension !== '' ? $originalExtension : 'jpg';
        if (!in_array($extension, $allowedExtensions, true)) {
            $extension = 'jpg';
        }
        $directory = dirname(__DIR__, 2)
            .DIRECTORY_SEPARATOR.'public'
            .DIRECTORY_SEPARATOR.'uploads'
            .DIRECTORY_SEPARATOR.'admin-profile';

        if (!is_dir($directory)) {
            mkdir($directory, 0777, true);
        }

        $filename = sprintf('admin-%s-%s.%s', $userId, bin2hex(random_bytes(6)), $extension);
        $photo->move($directory, $filename);

        $photos = $this->loadProfilePhotos();
        $oldUrl = $photos[$userId] ?? null;
        if (is_string($oldUrl) && str_starts_with($oldUrl, '/uploads/admin-profile/')) {
            $oldPath = dirname(__DIR__, 2).DIRECTORY_SEPARATOR.'public'.DIRECTORY_SEPARATOR.ltrim(str_replace('/', DIRECTORY_SEPARATOR, substr($oldUrl, 1)), DIRECTORY_SEPARATOR);
            if (is_file($oldPath)) {
                @unlink($oldPath);
            }
        }

        $photos[$userId] = '/uploads/admin-profile/'.$filename;
        $this->saveProfilePhotos($photos);
    }

    private function getProfilePhotosPath(): string
    {
        return dirname(__DIR__, 2).DIRECTORY_SEPARATOR.'var'.DIRECTORY_SEPARATOR.'admin-profile-photos.json';
    }

    private function mapUploadError(int $errorCode): string
    {
        return match ($errorCode) {
            UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'Le fichier est trop volumineux.',
            UPLOAD_ERR_PARTIAL => 'Le televersement du fichier a ete interrompu.',
            UPLOAD_ERR_NO_FILE => 'Aucun fichier n a ete selectionne.',
            UPLOAD_ERR_NO_TMP_DIR => 'Le dossier temporaire du serveur est introuvable.',
            UPLOAD_ERR_CANT_WRITE => 'Le serveur ne peut pas ecrire le fichier.',
            UPLOAD_ERR_EXTENSION => 'Une extension PHP a bloque le televersement.',
            default => 'Erreur inconnue lors du televersement.',
        };
    }
}
