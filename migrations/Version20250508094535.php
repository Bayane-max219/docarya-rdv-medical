<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250508094535 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE gestion_agenda DROP INDEX UNIQ_15A364DA8A49CC82, ADD INDEX IDX_15A364DA8A49CC82 (professionnel_id)');
        $this->addSql('ALTER TABLE gestion_agenda DROP FOREIGN KEY FK_15A364DA8A49CC82');
        $this->addSql('ALTER TABLE gestion_agenda ADD date_debut_indispo DATETIME NOT NULL, ADD date_fin_indispo DATETIME NOT NULL, ADD motif LONGTEXT DEFAULT NULL, DROP indisponibilites, DROP exceptions_disponibilite, DROP duree_rdv_par_defaut');
        $this->addSql('ALTER TABLE gestion_agenda ADD CONSTRAINT FK_15A364DA8A49CC82 FOREIGN KEY (professionnel_id) REFERENCES professionnel_de_sante (id)');
        $this->addSql('ALTER TABLE rendez_vous DROP fin_rdv');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE gestion_agenda DROP INDEX IDX_15A364DA8A49CC82, ADD UNIQUE INDEX UNIQ_15A364DA8A49CC82 (professionnel_id)');
        $this->addSql('ALTER TABLE gestion_agenda DROP FOREIGN KEY FK_15A364DA8A49CC82');
        $this->addSql('ALTER TABLE gestion_agenda ADD indisponibilites LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:json)\', ADD exceptions_disponibilite LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:json)\', ADD duree_rdv_par_defaut INT DEFAULT 30 NOT NULL, DROP date_debut_indispo, DROP date_fin_indispo, DROP motif');
        $this->addSql('ALTER TABLE gestion_agenda ADD CONSTRAINT FK_15A364DA8A49CC82 FOREIGN KEY (professionnel_id) REFERENCES professionnel_de_sante (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE rendez_vous ADD fin_rdv DATETIME NOT NULL');
    }
}
