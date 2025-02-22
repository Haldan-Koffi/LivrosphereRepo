<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241225125729 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE recommandation ADD livre_id INT NOT NULL, ADD utilisateur_id INT NOT NULL');
        $this->addSql('ALTER TABLE recommandation ADD CONSTRAINT FK_C7782A2837D925CB FOREIGN KEY (livre_id) REFERENCES livre (id)');
        $this->addSql('ALTER TABLE recommandation ADD CONSTRAINT FK_C7782A28FB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id)');
        $this->addSql('CREATE INDEX IDX_C7782A2837D925CB ON recommandation (livre_id)');
        $this->addSql('CREATE INDEX IDX_C7782A28FB88E14F ON recommandation (utilisateur_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE recommandation DROP FOREIGN KEY FK_C7782A2837D925CB');
        $this->addSql('ALTER TABLE recommandation DROP FOREIGN KEY FK_C7782A28FB88E14F');
        $this->addSql('DROP INDEX IDX_C7782A2837D925CB ON recommandation');
        $this->addSql('DROP INDEX IDX_C7782A28FB88E14F ON recommandation');
        $this->addSql('ALTER TABLE recommandation DROP livre_id, DROP utilisateur_id');
    }
}
