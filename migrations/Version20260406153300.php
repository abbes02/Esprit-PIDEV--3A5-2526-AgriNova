<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Add enhanced fields to panne table: severity, panne_type, priority, estimated_cost, reported_by_name
 */
final class Version20260406153300 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add severity, panne_type, priority, estimated_cost, and reported_by_name fields to panne table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE panne ADD severity VARCHAR(20) DEFAULT \'minor\'');
        $this->addSql('ALTER TABLE panne ADD panne_type VARCHAR(30) DEFAULT \'other\'');
        $this->addSql('ALTER TABLE panne ADD priority INT DEFAULT 3');
        $this->addSql('ALTER TABLE panne ADD estimated_cost DOUBLE PRECISION DEFAULT NULL');
        $this->addSql('ALTER TABLE panne ADD reported_by_name VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE panne DROP COLUMN severity');
        $this->addSql('ALTER TABLE panne DROP COLUMN panne_type');
        $this->addSql('ALTER TABLE panne DROP COLUMN priority');
        $this->addSql('ALTER TABLE panne DROP COLUMN estimated_cost');
        $this->addSql('ALTER TABLE panne DROP COLUMN reported_by_name');
    }
}
