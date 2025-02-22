<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241225125459 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE interaction_jaime ADD livre_id INT NOT NULL, ADD utilisateur_id INT NOT NULL');
        $this->addSql('ALTER TABLE interaction_jaime ADD CONSTRAINT FK_3C25BCD037D925CB FOREIGN KEY (livre_id) REFERENCES livre (id)');
        $this->addSql('ALTER TABLE interaction_jaime ADD CONSTRAINT FK_3C25BCD0FB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id)');
        $this->addSql('CREATE INDEX IDX_3C25BCD037D925CB ON interaction_jaime (livre_id)');
        $this->addSql('CREATE INDEX IDX_3C25BCD0FB88E14F ON interaction_jaime (utilisateur_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE interaction_jaime DROP FOREIGN KEY FK_3C25BCD037D925CB');
        $this->addSql('ALTER TABLE interaction_jaime DROP FOREIGN KEY FK_3C25BCD0FB88E14F');
        $this->addSql('DROP INDEX IDX_3C25BCD037D925CB ON interaction_jaime');
        $this->addSql('DROP INDEX IDX_3C25BCD0FB88E14F ON interaction_jaime');
        $this->addSql('ALTER TABLE interaction_jaime DROP livre_id, DROP utilisateur_id');
    }
}
