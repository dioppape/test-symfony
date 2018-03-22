<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180321234120 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE TABLE medecin_patient (medecin_id INT NOT NULL, patient_id INT NOT NULL, PRIMARY KEY(medecin_id, patient_id))');
        $this->addSql('CREATE INDEX IDX_64F312D64F31A84 ON medecin_patient (medecin_id)');
        $this->addSql('CREATE INDEX IDX_64F312D66B899279 ON medecin_patient (patient_id)');
        $this->addSql('ALTER TABLE medecin_patient ADD CONSTRAINT FK_64F312D64F31A84 FOREIGN KEY (medecin_id) REFERENCES medecin (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE medecin_patient ADD CONSTRAINT FK_64F312D66B899279 FOREIGN KEY (patient_id) REFERENCES patient (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE adresse ADD patient_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE adresse ADD CONSTRAINT FK_C35F08166B899279 FOREIGN KEY (patient_id) REFERENCES patient (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_C35F08166B899279 ON adresse (patient_id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP TABLE medecin_patient');
        $this->addSql('ALTER TABLE adresse DROP CONSTRAINT FK_C35F08166B899279');
        $this->addSql('DROP INDEX IDX_C35F08166B899279');
        $this->addSql('ALTER TABLE adresse DROP patient_id');
    }
}
