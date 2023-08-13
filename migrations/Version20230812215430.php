<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230812215430 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE candidate DROP FOREIGN KEY FK_C8B28E44296CD8AE');
        $this->addSql('ALTER TABLE candidate ADD CONSTRAINT FK_C8B28E44296CD8AE FOREIGN KEY (team_id) REFERENCES team (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_FAAAACDADE92C5CF ON school_year (annee)');
        $this->addSql('ALTER TABLE subject DROP FOREIGN KEY FK_FBCE3E7A444E1AE8');
        $this->addSql('ALTER TABLE subject DROP FOREIGN KEY FK_FBCE3E7AA76ED395');
        $this->addSql('ALTER TABLE subject ADD CONSTRAINT FK_FBCE3E7A444E1AE8 FOREIGN KEY (schoolyear_id) REFERENCES school_year (id)');
        $this->addSql('ALTER TABLE subject ADD CONSTRAINT FK_FBCE3E7AA76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE team DROP FOREIGN KEY team_ibfk_1');
        $this->addSql('ALTER TABLE team ADD CONSTRAINT FK_C4E0A61F23EDC87 FOREIGN KEY (subject_id) REFERENCES subject (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE candidate DROP FOREIGN KEY FK_C8B28E44296CD8AE');
        $this->addSql('ALTER TABLE candidate ADD CONSTRAINT FK_C8B28E44296CD8AE FOREIGN KEY (team_id) REFERENCES team (id) ON UPDATE SET NULL ON DELETE SET NULL');
        $this->addSql('DROP INDEX UNIQ_FAAAACDADE92C5CF ON school_year');
        $this->addSql('ALTER TABLE subject DROP FOREIGN KEY FK_FBCE3E7A444E1AE8');
        $this->addSql('ALTER TABLE subject DROP FOREIGN KEY FK_FBCE3E7AA76ED395');
        $this->addSql('ALTER TABLE subject ADD CONSTRAINT FK_FBCE3E7A444E1AE8 FOREIGN KEY (schoolyear_id) REFERENCES school_year (id) ON UPDATE SET NULL ON DELETE SET NULL');
        $this->addSql('ALTER TABLE subject ADD CONSTRAINT FK_FBCE3E7AA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON UPDATE SET NULL ON DELETE SET NULL');
        $this->addSql('ALTER TABLE team DROP FOREIGN KEY FK_C4E0A61F23EDC87');
        $this->addSql('ALTER TABLE team ADD CONSTRAINT team_ibfk_1 FOREIGN KEY (subject_id) REFERENCES subject (id) ON UPDATE SET NULL ON DELETE SET NULL');
    }
}
