<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250501025945 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE avis (id INT AUTO_INCREMENT NOT NULL, patient_id INT NOT NULL, professionnel_id INT NOT NULL, note INT NOT NULL, commentaire LONGTEXT DEFAULT NULL, INDEX IDX_8F91ABF06B899279 (patient_id), INDEX IDX_8F91ABF08A49CC82 (professionnel_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE gestion_agenda (id INT AUTO_INCREMENT NOT NULL, professionnel_id INT DEFAULT NULL, disponibilite DATETIME NOT NULL, UNIQUE INDEX UNIQ_15A364DA8A49CC82 (professionnel_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE rendez_vous (id INT AUTO_INCREMENT NOT NULL, patient_id INT DEFAULT NULL, professionnel_id INT DEFAULT NULL, date_heure DATETIME NOT NULL, statut VARCHAR(255) NOT NULL, motif VARCHAR(255) NOT NULL, INDEX IDX_65E8AA0A6B899279 (patient_id), INDEX IDX_65E8AA0A8A49CC82 (professionnel_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE avis ADD CONSTRAINT FK_8F91ABF06B899279 FOREIGN KEY (patient_id) REFERENCES patient (id)');
        $this->addSql('ALTER TABLE avis ADD CONSTRAINT FK_8F91ABF08A49CC82 FOREIGN KEY (professionnel_id) REFERENCES professionnel_de_sante (id)');
        $this->addSql('ALTER TABLE gestion_agenda ADD CONSTRAINT FK_15A364DA8A49CC82 FOREIGN KEY (professionnel_id) REFERENCES professionnel_de_sante (id)');
        $this->addSql('ALTER TABLE rendez_vous ADD CONSTRAINT FK_65E8AA0A6B899279 FOREIGN KEY (patient_id) REFERENCES patient (id)');
        $this->addSql('ALTER TABLE rendez_vous ADD CONSTRAINT FK_65E8AA0A8A49CC82 FOREIGN KEY (professionnel_id) REFERENCES professionnel_de_sante (id)');
        $this->addSql('ALTER TABLE professionnel_de_sante CHANGE photos photo VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE avis');
        $this->addSql('DROP TABLE gestion_agenda');
        $this->addSql('DROP TABLE rendez_vous');
        $this->addSql('ALTER TABLE professionnel_de_sante CHANGE photo photos VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`');
    }
}
