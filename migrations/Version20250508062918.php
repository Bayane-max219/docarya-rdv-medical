<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250508062918 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE gestion_agenda DROP FOREIGN KEY FK_15A364DA8A49CC82');
        $this->addSql('ALTER TABLE gestion_agenda ADD indisponibilites LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', ADD exceptions_disponibilite LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', ADD duree_rdv_par_defaut INT DEFAULT 30 NOT NULL, DROP disponibilite, CHANGE professionnel_id professionnel_id INT NOT NULL');
        $this->addSql('ALTER TABLE gestion_agenda ADD CONSTRAINT FK_15A364DA8A49CC82 FOREIGN KEY (professionnel_id) REFERENCES professionnel_de_sante (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE gestion_agenda DROP FOREIGN KEY FK_15A364DA8A49CC82');
        $this->addSql('ALTER TABLE gestion_agenda ADD disponibilite DATETIME NOT NULL, DROP indisponibilites, DROP exceptions_disponibilite, DROP duree_rdv_par_defaut, CHANGE professionnel_id professionnel_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE gestion_agenda ADD CONSTRAINT FK_15A364DA8A49CC82 FOREIGN KEY (professionnel_id) REFERENCES professionnel_de_sante (id)');
    }
}
