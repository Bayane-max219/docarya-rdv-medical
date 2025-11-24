<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250509085135 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE patient_partage_carnet (patient_id INT NOT NULL, professionnel_de_sante_id INT NOT NULL, INDEX IDX_793475596B899279 (patient_id), INDEX IDX_79347559C0EC2381 (professionnel_de_sante_id), PRIMARY KEY(patient_id, professionnel_de_sante_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE patient_partage_carnet ADD CONSTRAINT FK_793475596B899279 FOREIGN KEY (patient_id) REFERENCES patient (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE patient_partage_carnet ADD CONSTRAINT FK_79347559C0EC2381 FOREIGN KEY (professionnel_de_sante_id) REFERENCES professionnel_de_sante (id) ON DELETE CASCADE');
        $this->addSql('DROP TABLE demande_acces_historique');
        $this->addSql('ALTER TABLE patient ADD carnet_partage TINYINT(1) DEFAULT \'0\' NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE demande_acces_historique (id INT AUTO_INCREMENT NOT NULL, demandeur_id INT NOT NULL, patient_id INT NOT NULL, rendez_vous_lie_id INT DEFAULT NULL, statut VARCHAR(20) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_6834F82C95A6EE59 (demandeur_id), INDEX IDX_6834F82C6B899279 (patient_id), INDEX IDX_6834F82C3EB661C7 (rendez_vous_lie_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE demande_acces_historique ADD CONSTRAINT FK_6834F82C3EB661C7 FOREIGN KEY (rendez_vous_lie_id) REFERENCES rendez_vous (id)');
        $this->addSql('ALTER TABLE demande_acces_historique ADD CONSTRAINT FK_6834F82C6B899279 FOREIGN KEY (patient_id) REFERENCES patient (id)');
        $this->addSql('ALTER TABLE demande_acces_historique ADD CONSTRAINT FK_6834F82C95A6EE59 FOREIGN KEY (demandeur_id) REFERENCES professionnel_de_sante (id)');
        $this->addSql('DROP TABLE patient_partage_carnet');
        $this->addSql('ALTER TABLE patient DROP carnet_partage');
    }
}
