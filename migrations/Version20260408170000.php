<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260408170000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajoute la liaison parcelle -> utilisateur et retro-renseigne id_utilisateur quand possible.';
    }

    public function up(Schema $schema): void
    {
        $parcelleTable = $schema->getTable('parcelle');

        if (!$parcelleTable->hasColumn('id_utilisateur')) {
            $this->addSql('ALTER TABLE parcelle ADD id_utilisateur INT DEFAULT NULL');
        }

        if (!$parcelleTable->hasIndex('IDX_232B3188FB88E14F')) {
            $this->addSql('CREATE INDEX IDX_232B3188FB88E14F ON parcelle (id_utilisateur)');
        }

        $this->addSql("
            UPDATE parcelle p
            JOIN utilisateur u
              ON CONVERT(LOWER(TRIM(p.proprietaire)) USING utf8mb4) COLLATE utf8mb4_unicode_ci = CONVERT(LOWER(TRIM(CONCAT(u.prenom, ' ', u.nom))) USING utf8mb4) COLLATE utf8mb4_unicode_ci
              OR CONVERT(LOWER(TRIM(p.proprietaire)) USING utf8mb4) COLLATE utf8mb4_unicode_ci = CONVERT(LOWER(TRIM(CONCAT(u.nom, ' ', u.prenom))) USING utf8mb4) COLLATE utf8mb4_unicode_ci
              OR CONVERT(LOWER(TRIM(p.proprietaire)) USING utf8mb4) COLLATE utf8mb4_unicode_ci = CONVERT(LOWER(TRIM(u.prenom)) USING utf8mb4) COLLATE utf8mb4_unicode_ci
              OR CONVERT(LOWER(TRIM(p.proprietaire)) USING utf8mb4) COLLATE utf8mb4_unicode_ci = CONVERT(LOWER(TRIM(u.nom)) USING utf8mb4) COLLATE utf8mb4_unicode_ci
              OR CONVERT(LOWER(TRIM(p.proprietaire)) USING utf8mb4) COLLATE utf8mb4_unicode_ci = CONVERT(LOWER(TRIM(u.email)) USING utf8mb4) COLLATE utf8mb4_unicode_ci
            SET p.id_utilisateur = u.id_utilisateur
            WHERE p.id_utilisateur IS NULL
        ");

        if (!$parcelleTable->hasForeignKey('FK_232B3188FB88E14F')) {
            $this->addSql('ALTER TABLE parcelle ADD CONSTRAINT FK_232B3188FB88E14F FOREIGN KEY (id_utilisateur) REFERENCES utilisateur (id_utilisateur)');
        }
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE parcelle DROP FOREIGN KEY FK_232B3188FB88E14F');
        $this->addSql('DROP INDEX IDX_232B3188FB88E14F ON parcelle');
        $this->addSql('ALTER TABLE parcelle DROP id_utilisateur');
    }
}
