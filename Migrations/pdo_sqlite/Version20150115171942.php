<?php

namespace Laurent\PiaBundle\Migrations\pdo_sqlite;

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
                id INTEGER NOT NULL, 
                name VARCHAR(255) NOT NULL, 
                description CLOB NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE TABLE laurent_pia_action_user (
                actions_id INTEGER NOT NULL, 
                user_id INTEGER NOT NULL, 
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
                id INTEGER NOT NULL, 
                intervenant_id INTEGER DEFAULT NULL, 
                taches_id INTEGER DEFAULT NULL, 
                description CLOB NOT NULL, 
                date DATETIME DEFAULT NULL, 
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
                id INTEGER NOT NULL, 
                action_id INTEGER DEFAULT NULL, 
                responsable_id INTEGER DEFAULT NULL, 
                eleves_id INTEGER DEFAULT NULL, 
                titre VARCHAR(255) DEFAULT NULL, 
                commentaire CLOB DEFAULT NULL, 
                fini BOOLEAN DEFAULT '0' NOT NULL, 
                priorite INTEGER DEFAULT NULL, 
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
                taches_id INTEGER NOT NULL, 
                user_id INTEGER NOT NULL, 
                PRIMARY KEY(taches_id, user_id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_97513425B8A61670 ON laurent_pia_taches_user (taches_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_97513425A76ED395 ON laurent_pia_taches_user (user_id)
        ");
    }

    public function down(Schema $schema)
    {
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