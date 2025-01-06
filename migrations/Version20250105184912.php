<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250105184912 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE reinitialisation_mot_de_passe (id INT AUTO_INCREMENT NOT NULL, utilisateur_id INT NOT NULL, selecteur VARCHAR(20) NOT NULL, htoken VARCHAR(100) NOT NULL, date_demande DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', date_expiration DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_3F694751FB88E14F (utilisateur_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE reinitialisation_mot_de_passe ADD CONSTRAINT FK_3F694751FB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE reinitialisation_mot_de_passe DROP FOREIGN KEY FK_3F694751FB88E14F');
        $this->addSql('DROP TABLE reinitialisation_mot_de_passe');
    }
}
