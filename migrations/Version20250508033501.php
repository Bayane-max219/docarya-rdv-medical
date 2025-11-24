<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250508033501 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE consultation (id INT AUTO_INCREMENT NOT NULL, rendez_vous_id INT NOT NULL, date DATETIME NOT NULL, notes LONGTEXT DEFAULT NULL, ordonnances LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:json)\', partage_autorise TINYINT(1) DEFAULT \'0\' NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', UNIQUE INDEX UNIQ_964685A691EF7EAA (rendez_vous_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE consultation_partage_professionnels (consultation_id INT NOT NULL, professionnel_de_sante_id INT NOT NULL, INDEX IDX_F6E7941262FF6CDF (consultation_id), INDEX IDX_F6E79412C0EC2381 (professionnel_de_sante_id), PRIMARY KEY(consultation_id, professionnel_de_sante_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE demande_acces_historique (id INT AUTO_INCREMENT NOT NULL, demandeur_id INT NOT NULL, patient_id INT NOT NULL, rendez_vous_lie_id INT DEFAULT NULL, statut VARCHAR(20) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_6834F82C95A6EE59 (demandeur_id), INDEX IDX_6834F82C6B899279 (patient_id), INDEX IDX_6834F82C3EB661C7 (rendez_vous_lie_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE consultation ADD CONSTRAINT FK_964685A691EF7EAA FOREIGN KEY (rendez_vous_id) REFERENCES rendez_vous (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE consultation_partage_professionnels ADD CONSTRAINT FK_F6E7941262FF6CDF FOREIGN KEY (consultation_id) REFERENCES consultation (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE consultation_partage_professionnels ADD CONSTRAINT FK_F6E79412C0EC2381 FOREIGN KEY (professionnel_de_sante_id) REFERENCES professionnel_de_sante (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE demande_acces_historique ADD CONSTRAINT FK_6834F82C95A6EE59 FOREIGN KEY (demandeur_id) REFERENCES professionnel_de_sante (id)');
        $this->addSql('ALTER TABLE demande_acces_historique ADD CONSTRAINT FK_6834F82C6B899279 FOREIGN KEY (patient_id) REFERENCES patient (id)');
        $this->addSql('ALTER TABLE demande_acces_historique ADD CONSTRAINT FK_6834F82C3EB661C7 FOREIGN KEY (rendez_vous_lie_id) REFERENCES rendez_vous (id)');
        $this->addSql('DROP TABLE messenger_messages');
        $this->addSql('ALTER TABLE professionnel_de_sante DROP horaires_travail');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE consultation_partage_professionnels DROP FOREIGN KEY FK_F6E7941262FF6CDF');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, headers LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, queue_name VARCHAR(190) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E016BA31DB (delivered_at), INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('DROP TABLE consultation');
        $this->addSql('DROP TABLE consultation_partage_professionnels');
        $this->addSql('DROP TABLE demande_acces_historique');
        $this->addSql('ALTER TABLE professionnel_de_sante ADD horaires_travail LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:json)\'');
    }
}
