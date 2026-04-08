<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260408162457 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE achatformation DROP FOREIGN KEY `achatformation_ibfk_1`');
        $this->addSql('ALTER TABLE achatformation DROP FOREIGN KEY `achatformation_ibfk_2`');
        $this->addSql('ALTER TABLE produit DROP FOREIGN KEY `fk_utilisateurProduit`');
        $this->addSql('ALTER TABLE produit_agricole DROP FOREIGN KEY `produit_agricole_ibfk_1`');
        $this->addSql('ALTER TABLE produit_iot DROP FOREIGN KEY `fk_produit_iot`');
        $this->addSql('DROP TABLE achatformation');
        $this->addSql('DROP TABLE produit');
        $this->addSql('DROP TABLE produit_agricole');
        $this->addSql('DROP TABLE produit_iot');
        $this->addSql('ALTER TABLE evenement DROP FOREIGN KEY `evenement_ibfk_1`');
        $this->addSql('DROP INDEX IdFormation ON evenement');
        $this->addSql('ALTER TABLE evenement MODIFY IdEvenement INT NOT NULL');
        $this->addSql('ALTER TABLE evenement ADD date_debut DATETIME NOT NULL, ADD date_fin DATETIME NOT NULL, ADD nombre_inscrits INT NOT NULL, ADD formation_id INT NOT NULL, DROP DateDebut, DROP DateFin, DROP CapaciteMax, DROP NombreInscrits, CHANGE Lieu lieu VARCHAR(255) NOT NULL, CHANGE Type type VARCHAR(50) NOT NULL, CHANGE Statut statut VARCHAR(50) NOT NULL, CHANGE IdEvenement id INT AUTO_INCREMENT NOT NULL, CHANGE IdFormation capacite_max INT NOT NULL, DROP PRIMARY KEY, ADD PRIMARY KEY (id)');
        $this->addSql('ALTER TABLE evenement ADD CONSTRAINT FK_B26681E5200282E FOREIGN KEY (formation_id) REFERENCES formation (id)');
        $this->addSql('CREATE INDEX IDX_B26681E5200282E ON evenement (formation_id)');
        $this->addSql('ALTER TABLE formation MODIFY IdFormation INT NOT NULL');
        $this->addSql('ALTER TABLE formation ADD duree_heures INT NOT NULL, ADD date_creation DATE NOT NULL, DROP DureeHeures, DROP DateCreation, DROP Formateur, DROP Image, CHANGE Titre titre VARCHAR(255) NOT NULL, CHANGE Description description LONGTEXT NOT NULL, CHANGE Domaine domaine VARCHAR(100) NOT NULL, CHANGE Niveau niveau VARCHAR(50) NOT NULL, CHANGE Prix prix DOUBLE PRECISION NOT NULL, CHANGE Statut statut VARCHAR(50) NOT NULL, CHANGE IdFormation id INT AUTO_INCREMENT NOT NULL, DROP PRIMARY KEY, ADD PRIMARY KEY (id)');
        $this->addSql('ALTER TABLE parcelle DROP FOREIGN KEY `FK_232B3188FB88E14F`');
        $this->addSql('ALTER TABLE parcelle CHANGE id_utilisateur id_utilisateur INT NOT NULL');
        $this->addSql('DROP INDEX idx_232b3188fb88e14f ON parcelle');
        $this->addSql('CREATE INDEX IDX_C56E2CF650EAE44 ON parcelle (id_utilisateur)');
        $this->addSql('ALTER TABLE parcelle ADD CONSTRAINT `FK_232B3188FB88E14F` FOREIGN KEY (id_utilisateur) REFERENCES utilisateur (id_utilisateur)');
        $this->addSql('ALTER TABLE plante DROP FOREIGN KEY `FK_C9F2A12483FCF4FC`');
        $this->addSql('DROP INDEX idx_c9f2a12483fcf4fc ON plante');
        $this->addSql('CREATE INDEX IDX_517A6947D990057D ON plante (IdParcelle)');
        $this->addSql('ALTER TABLE plante ADD CONSTRAINT `FK_C9F2A12483FCF4FC` FOREIGN KEY (IdParcelle) REFERENCES parcelle (IdParcelle)');
        $this->addSql('DROP INDEX email ON utilisateur');
        $this->addSql('ALTER TABLE utilisateur DROP photo_profil, DROP dernier_login, CHANGE role role VARCHAR(20) NOT NULL, CHANGE statut statut VARCHAR(20) DEFAULT \'ACTIF\' NOT NULL, CHANGE date_creation date_creation DATETIME DEFAULT NULL, CHANGE points_cadeaux points_cadeaux INT DEFAULT 0 NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE achatformation (IdAchat INT AUTO_INCREMENT NOT NULL, IdFormation INT NOT NULL, IdUtilisateur INT NOT NULL, DateAchat DATETIME DEFAULT CURRENT_TIMESTAMP, Montant DOUBLE PRECISION NOT NULL, MethodePaiement ENUM(\'Carte\', \'Virement\', \'Espèces\', \'Points Cadeaux\') CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, StatutPaiement ENUM(\'Payé\', \'En attente\', \'Annulé\') CHARACTER SET utf8mb4 DEFAULT \'En attente\' COLLATE `utf8mb4_general_ci`, INDEX IdUtilisateur (IdUtilisateur), UNIQUE INDEX unique_achat (IdFormation, IdUtilisateur), INDEX IDX_95BFE2F53A36853E (IdFormation), PRIMARY KEY (IdAchat)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE produit (IdProduit INT AUTO_INCREMENT NOT NULL, Ref VARCHAR(50) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, Nom VARCHAR(100) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, Image VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, Description TEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, Quantite INT DEFAULT NULL, Prix DOUBLE PRECISION DEFAULT NULL, NbrLike INT DEFAULT 0, DateAjout DATETIME DEFAULT CURRENT_TIMESTAMP, Proprietaire INT DEFAULT NULL, TypeProduit ENUM(\'IOT\', \'Agricole\') CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, UNIQUE INDEX uq_produit_ref (Ref), INDEX fk_utilisateurProduit (Proprietaire), PRIMARY KEY (IdProduit)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE produit_agricole (IdProduit INT NOT NULL, CategorieAgricole VARCHAR(100) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, DateExpiration DATE DEFAULT NULL, PRIMARY KEY (IdProduit)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE produit_iot (IdProduit INT NOT NULL, Categorie ENUM(\'Capteur Température\', \'Capteur Humidité Air\', \'Capteur Humidité Sol\', \'Capteur Lumière\', \'Irrigation\', \'Autre\') CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, PRIMARY KEY (IdProduit)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE achatformation ADD CONSTRAINT `achatformation_ibfk_1` FOREIGN KEY (IdFormation) REFERENCES formation (IdFormation) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE achatformation ADD CONSTRAINT `achatformation_ibfk_2` FOREIGN KEY (IdUtilisateur) REFERENCES utilisateur (id_utilisateur) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE produit ADD CONSTRAINT `fk_utilisateurProduit` FOREIGN KEY (Proprietaire) REFERENCES utilisateur (id_utilisateur) ON UPDATE CASCADE ON DELETE SET NULL');
        $this->addSql('ALTER TABLE produit_agricole ADD CONSTRAINT `produit_agricole_ibfk_1` FOREIGN KEY (IdProduit) REFERENCES produit (IdProduit) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE produit_iot ADD CONSTRAINT `fk_produit_iot` FOREIGN KEY (IdProduit) REFERENCES produit (IdProduit) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE evenement DROP FOREIGN KEY FK_B26681E5200282E');
        $this->addSql('DROP INDEX IDX_B26681E5200282E ON evenement');
        $this->addSql('ALTER TABLE evenement MODIFY id INT NOT NULL');
        $this->addSql('ALTER TABLE evenement ADD IdFormation INT NOT NULL, ADD DateDebut DATETIME DEFAULT NULL, ADD DateFin DATETIME DEFAULT NULL, ADD CapaciteMax INT DEFAULT NULL, ADD NombreInscrits INT DEFAULT 0, DROP date_debut, DROP date_fin, DROP capacite_max, DROP nombre_inscrits, DROP formation_id, CHANGE lieu Lieu VARCHAR(200) DEFAULT NULL, CHANGE type Type ENUM(\'Présentiel\', \'En ligne\') DEFAULT NULL, CHANGE statut Statut ENUM(\'Prévu\', \'En cours\', \'Terminé\', \'Annulé\') DEFAULT \'Prévu\', CHANGE id IdEvenement INT AUTO_INCREMENT NOT NULL, DROP PRIMARY KEY, ADD PRIMARY KEY (IdEvenement)');
        $this->addSql('ALTER TABLE evenement ADD CONSTRAINT `evenement_ibfk_1` FOREIGN KEY (IdFormation) REFERENCES formation (IdFormation) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IdFormation ON evenement (IdFormation)');
        $this->addSql('ALTER TABLE formation MODIFY id INT NOT NULL');
        $this->addSql('ALTER TABLE formation ADD DureeHeures INT DEFAULT NULL, ADD DateCreation DATE DEFAULT CURRENT_DATE, ADD Formateur INT DEFAULT NULL, ADD Image VARCHAR(255) DEFAULT NULL, DROP duree_heures, DROP date_creation, CHANGE titre Titre VARCHAR(150) NOT NULL, CHANGE description Description TEXT DEFAULT NULL, CHANGE domaine Domaine VARCHAR(100) DEFAULT NULL, CHANGE niveau Niveau ENUM(\'Débutant\', \'Intermédiaire\', \'Avancé\') DEFAULT NULL, CHANGE prix Prix DOUBLE PRECISION DEFAULT NULL, CHANGE statut Statut ENUM(\'Active\', \'Inactive\') DEFAULT \'Active\', CHANGE id IdFormation INT AUTO_INCREMENT NOT NULL, DROP PRIMARY KEY, ADD PRIMARY KEY (IdFormation)');
        $this->addSql('ALTER TABLE parcelle DROP FOREIGN KEY FK_C56E2CF650EAE44');
        $this->addSql('ALTER TABLE parcelle CHANGE id_utilisateur id_utilisateur INT DEFAULT NULL');
        $this->addSql('DROP INDEX idx_c56e2cf650eae44 ON parcelle');
        $this->addSql('CREATE INDEX IDX_232B3188FB88E14F ON parcelle (id_utilisateur)');
        $this->addSql('ALTER TABLE parcelle ADD CONSTRAINT FK_C56E2CF650EAE44 FOREIGN KEY (id_utilisateur) REFERENCES utilisateur (id_utilisateur)');
        $this->addSql('ALTER TABLE plante DROP FOREIGN KEY FK_517A6947D990057D');
        $this->addSql('DROP INDEX idx_517a6947d990057d ON plante');
        $this->addSql('CREATE INDEX IDX_C9F2A12483FCF4FC ON plante (IdParcelle)');
        $this->addSql('ALTER TABLE plante ADD CONSTRAINT FK_517A6947D990057D FOREIGN KEY (IdParcelle) REFERENCES parcelle (IdParcelle)');
        $this->addSql('ALTER TABLE utilisateur ADD photo_profil LONGBLOB DEFAULT NULL, ADD dernier_login DATETIME DEFAULT NULL, CHANGE role role ENUM(\'ADMIN\', \'AGRICULTEUR\', \'CLIENT\', \'LIVREUR\') NOT NULL, CHANGE statut statut ENUM(\'ACTIF\', \'DESACTIVE\') DEFAULT \'ACTIF\', CHANGE points_cadeaux points_cadeaux DOUBLE PRECISION DEFAULT \'0\', CHANGE date_creation date_creation DATETIME DEFAULT CURRENT_TIMESTAMP');
        $this->addSql('CREATE UNIQUE INDEX email ON utilisateur (email)');
    }
}
