<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241214182902 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX nom ON categorie');
        $this->addSql('ALTER TABLE livre ADD categorie_id INT NOT NULL, ADD utilisateur_id INT NOT NULL');
        $this->addSql('ALTER TABLE livre ADD CONSTRAINT FK_AC634F99BCF5E72D FOREIGN KEY (categorie_id) REFERENCES categorie (id)');
        $this->addSql('ALTER TABLE livre ADD CONSTRAINT FK_AC634F99FB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id)');
        $this->addSql('CREATE INDEX IDX_AC634F99BCF5E72D ON livre (categorie_id)');
        $this->addSql('CREATE INDEX IDX_AC634F99FB88E14F ON livre (utilisateur_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE UNIQUE INDEX nom ON categorie (nom)');
        $this->addSql('ALTER TABLE livre DROP FOREIGN KEY FK_AC634F99BCF5E72D');
        $this->addSql('ALTER TABLE livre DROP FOREIGN KEY FK_AC634F99FB88E14F');
        $this->addSql('DROP INDEX IDX_AC634F99BCF5E72D ON livre');
        $this->addSql('DROP INDEX IDX_AC634F99FB88E14F ON livre');
        $this->addSql('ALTER TABLE livre DROP categorie_id, DROP utilisateur_id');
    }
}
