<?php

namespace Laurent\PiaBundle\Migrations\pdo_pgsql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/01/15 05:19:43
 */
class Version20150115171942 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE laurent_pia_action (
                id SERIAL NOT NULL, 
                name VARCHAR(255) NOT NULL, 
                description TEXT NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE TABLE laurent_pia_action_user (
                actions_id INT NOT NULL, 
                user_id INT NOT NULL, 
                PRIMARY KEY(actions_id, user_id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_5336EDBCB15F4BF6 ON laurent_pia_action_user (actions_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_5336EDBCA76ED395 ON laurent_pia_action_user (user_id)
        ");
        $this->addSql("
            CREATE TABLE laurent_pia_suivi (
                id SERIAL NOT NULL, 
                intervenant_id INT DEFAULT NULL, 
                taches_id INT DEFAULT NULL, 
                description TEXT NOT NULL, 
                date TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_553A5370AB9A1716 ON laurent_pia_suivi (intervenant_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_553A5370B8A61670 ON laurent_pia_suivi (taches_id)
        ");
        $this->addSql("
            CREATE TABLE laurent_pia_taches (
                id SERIAL NOT NULL, 
                action_id INT DEFAULT NULL, 
                responsable_id INT DEFAULT NULL, 
                eleves_id INT DEFAULT NULL, 
                titre VARCHAR(255) DEFAULT NULL, 
                commentaire TEXT DEFAULT NULL, 
                fini BOOLEAN DEFAULT 'false' NOT NULL, 
                priorite INT DEFAULT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_168BA48C9D32F035 ON laurent_pia_taches (action_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_168BA48C53C59D72 ON laurent_pia_taches (responsable_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_168BA48CC2140342 ON laurent_pia_taches (eleves_id)
        ");
        $this->addSql("
            CREATE TABLE laurent_pia_taches_user (
                taches_id INT NOT NULL, 
                user_id INT NOT NULL, 
                PRIMARY KEY(taches_id, user_id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_97513425B8A61670 ON laurent_pia_taches_user (taches_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_97513425A76ED395 ON laurent_pia_taches_user (user_id)
        ");
        $this->addSql("
            ALTER TABLE laurent_pia_action_user 
            ADD CONSTRAINT FK_5336EDBCB15F4BF6 FOREIGN KEY (actions_id) 
            REFERENCES laurent_pia_action (id) 
            ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        ");
        $this->addSql("
            ALTER TABLE laurent_pia_action_user 
            ADD CONSTRAINT FK_5336EDBCA76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        ");
        $this->addSql("
            ALTER TABLE laurent_pia_suivi 
            ADD CONSTRAINT FK_553A5370AB9A1716 FOREIGN KEY (intervenant_id) 
            REFERENCES claro_user (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        ");
        $this->addSql("
            ALTER TABLE laurent_pia_suivi 
            ADD CONSTRAINT FK_553A5370B8A61670 FOREIGN KEY (taches_id) 
            REFERENCES laurent_pia_taches (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        ");
        $this->addSql("
            ALTER TABLE laurent_pia_taches 
            ADD CONSTRAINT FK_168BA48C9D32F035 FOREIGN KEY (action_id) 
            REFERENCES laurent_pia_action (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        ");
        $this->addSql("
            ALTER TABLE laurent_pia_taches 
            ADD CONSTRAINT FK_168BA48C53C59D72 FOREIGN KEY (responsable_id) 
            REFERENCES claro_user (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        ");
        $this->addSql("
            ALTER TABLE laurent_pia_taches 
            ADD CONSTRAINT FK_168BA48CC2140342 FOREIGN KEY (eleves_id) 
            REFERENCES claro_user (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        ");
        $this->addSql("
            ALTER TABLE laurent_pia_taches_user 
            ADD CONSTRAINT FK_97513425B8A61670 FOREIGN KEY (taches_id) 
            REFERENCES laurent_pia_taches (id) 
            ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        ");
        $this->addSql("
            ALTER TABLE laurent_pia_taches_user 
            ADD CONSTRAINT FK_97513425A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE laurent_pia_action_user 
            DROP CONSTRAINT FK_5336EDBCB15F4BF6
        ");
        $this->addSql("
            ALTER TABLE laurent_pia_taches 
            DROP CONSTRAINT FK_168BA48C9D32F035
        ");
        $this->addSql("
            ALTER TABLE laurent_pia_suivi 
            DROP CONSTRAINT FK_553A5370B8A61670
        ");
        $this->addSql("
            ALTER TABLE laurent_pia_taches_user 
            DROP CONSTRAINT FK_97513425B8A61670
        ");
        $this->addSql("
            DROP TABLE laurent_pia_action
        ");
        $this->addSql("
            DROP TABLE laurent_pia_action_user
        ");
        $this->addSql("
            DROP TABLE laurent_pia_suivi
        ");
        $this->addSql("
            DROP TABLE laurent_pia_taches
        ");
        $this->addSql("
            DROP TABLE laurent_pia_taches_user
        ");
    }
}