<?php

namespace App\Security;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

final class DatabaseUserAuthenticator
{
    public function __construct(
        private readonly Connection $connection,
        #[Autowire('%env(resolve:AUTH_USER_TABLE)%')] private readonly string $tableName,
        #[Autowire('%env(resolve:AUTH_USER_ID_COLUMN)%')] private readonly string $idColumn,
        #[Autowire('%env(resolve:AUTH_USER_EMAIL_COLUMN)%')] private readonly string $emailColumn,
        #[Autowire('%env(resolve:AUTH_USER_PASSWORD_COLUMN)%')] private readonly string $passwordColumn,
        #[Autowire('%env(resolve:AUTH_USER_DISPLAY_COLUMN)%')] private readonly string $displayColumn,
        #[Autowire('%env(default::AUTH_USER_ROLE_COLUMN)%')] private readonly ?string $roleColumn,
        #[Autowire('%env(default::AUTH_USER_STATUS_COLUMN)%')] private readonly ?string $statusColumn,
        #[Autowire('%env(default::AUTH_USER_ACTIVE_VALUE)%')] private readonly ?string $activeStatusValue,
        #[Autowire('%env(bool:AUTH_PASSWORD_MIGRATE_PLAINTEXT)%')] private readonly bool $migratePlaintextPassword,
    ) {
    }

    public function authenticate(string $email, string $plainPassword): AuthenticationResult
    {
        if ($email === '' || $plainPassword === '') {
            return AuthenticationResult::failure('Email et mot de passe obligatoires.');
        }

        try {
            $queryBuilder = $this->connection->createQueryBuilder()
                ->select(
                    sprintf('u.%s AS auth_id', $this->quoteIdentifier($this->idColumn)),
                    sprintf('u.%s AS auth_email', $this->quoteIdentifier($this->emailColumn)),
                    sprintf('u.%s AS auth_password', $this->quoteIdentifier($this->passwordColumn)),
                    sprintf('u.%s AS auth_display_name', $this->quoteIdentifier($this->displayColumn)),
                    $this->roleColumn !== null && $this->roleColumn !== ''
                        ? sprintf('u.%s AS auth_role', $this->quoteIdentifier($this->roleColumn))
                        : '\'\' AS auth_role',
                )
                ->from($this->quoteIdentifier($this->tableName), 'u')
                ->where(sprintf('LOWER(u.%s) = LOWER(:email)', $this->quoteIdentifier($this->emailColumn)))
                ->setParameter('email', $email)
                ->setMaxResults(1);

            if ($this->statusColumn !== null && $this->statusColumn !== '' && $this->activeStatusValue !== null && $this->activeStatusValue !== '') {
                $queryBuilder
                    ->andWhere(sprintf('u.%s = :active_status', $this->quoteIdentifier($this->statusColumn)))
                    ->setParameter('active_status', $this->activeStatusValue);
            }

            $row = $queryBuilder->fetchAssociative();
        } catch (Exception $exception) {
            return AuthenticationResult::failure(
                'Connexion base indisponible ou table utilisateurs introuvable. Verifie DATABASE_URL et la configuration AUTH_USER_* dans .env.'
            );
        }

        if ($row === false) {
            return AuthenticationResult::failure('Email ou mot de passe invalide.');
        }

        $storedPassword = (string) ($row['auth_password'] ?? '');

        if ($storedPassword === '') {
            return AuthenticationResult::failure('Le compte utilisateur ne contient pas de mot de passe exploitable.');
        }

        if ($this->passwordMatches($plainPassword, $storedPassword)) {
            if ($this->migratePlaintextPassword && $plainPassword === $storedPassword) {
                $this->upgradePlaintextPassword((string) $row['auth_id'], $plainPassword);
            }

            $this->touchLastLogin((string) $row['auth_id']);

            $displayName = trim((string) ($row['auth_display_name'] ?? ''));
            if ($displayName === '') {
                $displayName = (string) ($row['auth_email'] ?? $email);
            }

            return AuthenticationResult::success(new AuthenticatedUser(
                (string) ($row['auth_id'] ?? ''),
                (string) ($row['auth_email'] ?? $email),
                $displayName,
                strtoupper(trim((string) ($row['auth_role'] ?? ''))),
            ));
        }

        return AuthenticationResult::failure('Email ou mot de passe invalide.');
    }

    private function passwordMatches(string $plainPassword, string $storedPassword): bool
    {
        $passwordInfo = password_get_info($storedPassword);

        if (($passwordInfo['algo'] ?? null) !== null) {
            return password_verify($plainPassword, $storedPassword);
        }

        return hash_equals($storedPassword, $plainPassword);
    }

    private function upgradePlaintextPassword(string $id, string $plainPassword): void
    {
        try {
            $this->connection->update(
                $this->tableName,
                [$this->passwordColumn => password_hash($plainPassword, PASSWORD_DEFAULT)],
                [$this->idColumn => $id],
            );
        } catch (\Throwable) {
            // Authentication should still succeed even if the hash upgrade fails.
        }
    }

    private function touchLastLogin(string $id): void
    {
        if ($id === '') {
            return;
        }

        try {
            $this->connection->update(
                $this->tableName,
                ['dernier_login' => (new \DateTimeImmutable())->format('Y-m-d H:i:s')],
                [$this->idColumn => $id],
            );
        } catch (\Throwable) {
            // Authentication should still succeed even if last login cannot be updated.
        }
    }

    private function quoteIdentifier(string $identifier): string
    {
        return $this->connection->quoteIdentifier($identifier);
    }
}
