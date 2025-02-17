<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250105194328 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE reinitialisation_mot_de_passe ADD requested_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', ADD expires_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', DROP date_demande, DROP date_expiration, CHANGE selecteur selector VARCHAR(20) NOT NULL, CHANGE htoken hashed_token VARCHAR(100) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE reinitialisation_mot_de_passe ADD date_demande DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', ADD date_expiration DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', DROP requested_at, DROP expires_at, CHANGE selector selecteur VARCHAR(20) NOT NULL, CHANGE hashed_token htoken VARCHAR(100) NOT NULL');
    }
}
