<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Platforms\AbstractMySQLPlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260409150000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajoute les tables materiel, location et panne pour le module GestionMateriel-web.';
    }

    public function up(Schema $schema): void
    {
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof AbstractMySQLPlatform,
            'Cette migration supporte uniquement MySQL/MariaDB.'
        );

        $this->addSql(<<<'SQL'
CREATE TABLE IF NOT EXISTS `materiel_web` (
    `id` INT AUTO_INCREMENT NOT NULL,
    `proprietaire_id` INT DEFAULT NULL,
    `nom` VARCHAR(255) NOT NULL,
    `type` VARCHAR(100) NOT NULL,
    `description` LONGTEXT DEFAULT NULL,
    `prix_location` DOUBLE PRECISION NOT NULL,
    `etat` VARCHAR(50) DEFAULT 'Disponible',
    `image_url` VARCHAR(255) DEFAULT NULL,
    `date_ajout` DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX `fk_materiel_web_proprietaire` (`proprietaire_id`),
    PRIMARY KEY(`id`),
    CONSTRAINT `FK_MATERIEL_WEB_PROPRIETAIRE` FOREIGN KEY (`proprietaire_id`) REFERENCES `utilisateur` (`id_utilisateur`) ON DELETE SET NULL
) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
SQL);

        $this->addSql(<<<'SQL'
CREATE TABLE IF NOT EXISTS `location_web` (
    `id` INT AUTO_INCREMENT NOT NULL,
    `materiel_id` INT NOT NULL,
    `utilisateur_id` INT NOT NULL,
    `date_debut` DATE NOT NULL,
    `date_fin` DATE NOT NULL,
    `montant_total` DOUBLE PRECISION NOT NULL,
    `statut` VARCHAR(50) DEFAULT 'En cours',
    INDEX `fk_location_web_materiel` (`materiel_id`),
    INDEX `fk_location_web_user` (`utilisateur_id`),
    PRIMARY KEY(`id`),
    CONSTRAINT `FK_LOCATION_WEB_MATERIEL` FOREIGN KEY (`materiel_id`) REFERENCES `materiel_web` (`id`) ON DELETE CASCADE,
    CONSTRAINT `FK_LOCATION_WEB_UTILISATEUR` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateur` (`id_utilisateur`) ON DELETE CASCADE
) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
SQL);

        $this->addSql(<<<'SQL'
CREATE TABLE IF NOT EXISTS `panne_web` (
    `id` INT AUTO_INCREMENT NOT NULL,
    `materiel_id` INT NOT NULL,
    `description_panne` LONGTEXT NOT NULL,
    `date_panne` DATE NOT NULL,
    `date_reparation` DATE DEFAULT NULL,
    `cout_reparation` DOUBLE PRECISION DEFAULT 0,
    `image_url` VARCHAR(512) DEFAULT NULL,
    `diagnostic_solution` LONGTEXT DEFAULT NULL,
    `severity` VARCHAR(20) DEFAULT 'minor',
    `panne_type` VARCHAR(30) DEFAULT 'other',
    `priority` INT DEFAULT 3,
    `estimated_cost` DOUBLE PRECISION DEFAULT NULL,
    `reported_by_name` VARCHAR(255) DEFAULT NULL,
    INDEX `fk_panne_web_materiel` (`materiel_id`),
    PRIMARY KEY(`id`),
    CONSTRAINT `FK_PANNE_WEB_MATERIEL` FOREIGN KEY (`materiel_id`) REFERENCES `materiel_web` (`id`) ON DELETE CASCADE
) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
SQL);
    }

    public function down(Schema $schema): void
    {
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof AbstractMySQLPlatform,
            'Cette migration supporte uniquement MySQL/MariaDB.'
        );

        $this->addSql('DROP TABLE IF EXISTS `panne_web`');
        $this->addSql('DROP TABLE IF EXISTS `location_web`');
        $this->addSql('DROP TABLE IF EXISTS `materiel_web`');
    }
}
