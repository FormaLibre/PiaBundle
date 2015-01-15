<?php

namespace Laurent\PiaBundle\Migrations\mysqli;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/01/15 05:19:44
 */
class Version20150115171942 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE laurent_pia_action (
                id INT AUTO_INCREMENT NOT NULL, 
                name VARCHAR(255) NOT NULL, 
                description LONGTEXT NOT NULL, 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            CREATE TABLE laurent_pia_action_user (
                actions_id INT NOT NULL, 
                user_id INT NOT NULL, 
                INDEX IDX_5336EDBCB15F4BF6 (actions_id), 
                INDEX IDX_5336EDBCA76ED395 (user_id), 
                PRIMARY KEY(actions_id, user_id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            CREATE TABLE laurent_pia_suivi (
                id INT AUTO_INCREMENT NOT NULL, 
                intervenant_id INT DEFAULT NULL, 
                taches_id INT DEFAULT NULL, 
                description LONGTEXT NOT NULL, 
                date DATETIME DEFAULT NULL, 
                INDEX IDX_553A5370AB9A1716 (intervenant_id), 
                INDEX IDX_553A5370B8A61670 (taches_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            CREATE TABLE laurent_pia_taches (
                id INT AUTO_INCREMENT NOT NULL, 
                action_id INT DEFAULT NULL, 
                responsable_id INT DEFAULT NULL, 
                eleves_id INT DEFAULT NULL, 
                titre VARCHAR(255) DEFAULT NULL, 
                commentaire LONGTEXT DEFAULT NULL, 
                fini TINYINT(1) DEFAULT '0' NOT NULL, 
                priorite INT DEFAULT NULL, 
                INDEX IDX_168BA48C9D32F035 (action_id), 
                INDEX IDX_168BA48C53C59D72 (responsable_id), 
                INDEX IDX_168BA48CC2140342 (eleves_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            CREATE TABLE laurent_pia_taches_user (
                taches_id INT NOT NULL, 
                user_id INT NOT NULL, 
                INDEX IDX_97513425B8A61670 (taches_id), 
                INDEX IDX_97513425A76ED395 (user_id), 
                PRIMARY KEY(taches_id, user_id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            ALTER TABLE laurent_pia_action_user 
            ADD CONSTRAINT FK_5336EDBCB15F4BF6 FOREIGN KEY (actions_id) 
            REFERENCES laurent_pia_action (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE laurent_pia_action_user 
            ADD CONSTRAINT FK_5336EDBCA76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE laurent_pia_suivi 
            ADD CONSTRAINT FK_553A5370AB9A1716 FOREIGN KEY (intervenant_id) 
            REFERENCES claro_user (id)
        ");
        $this->addSql("
            ALTER TABLE laurent_pia_suivi 
            ADD CONSTRAINT FK_553A5370B8A61670 FOREIGN KEY (taches_id) 
            REFERENCES laurent_pia_taches (id)
        ");
        $this->addSql("
            ALTER TABLE laurent_pia_taches 
            ADD CONSTRAINT FK_168BA48C9D32F035 FOREIGN KEY (action_id) 
            REFERENCES laurent_pia_action (id)
        ");
        $this->addSql("
            ALTER TABLE laurent_pia_taches 
            ADD CONSTRAINT FK_168BA48C53C59D72 FOREIGN KEY (responsable_id) 
            REFERENCES claro_user (id)
        ");
        $this->addSql("
            ALTER TABLE laurent_pia_taches 
            ADD CONSTRAINT FK_168BA48CC2140342 FOREIGN KEY (eleves_id) 
            REFERENCES claro_user (id)
        ");
        $this->addSql("
            ALTER TABLE laurent_pia_taches_user 
            ADD CONSTRAINT FK_97513425B8A61670 FOREIGN KEY (taches_id) 
            REFERENCES laurent_pia_taches (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE laurent_pia_taches_user 
            ADD CONSTRAINT FK_97513425A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE laurent_pia_action_user 
            DROP FOREIGN KEY FK_5336EDBCB15F4BF6
        ");
        $this->addSql("
            ALTER TABLE laurent_pia_taches 
            DROP FOREIGN KEY FK_168BA48C9D32F035
        ");
        $this->addSql("
            ALTER TABLE laurent_pia_suivi 
            DROP FOREIGN KEY FK_553A5370B8A61670
        ");
        $this->addSql("
            ALTER TABLE laurent_pia_taches_user 
            DROP FOREIGN KEY FK_97513425B8A61670
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