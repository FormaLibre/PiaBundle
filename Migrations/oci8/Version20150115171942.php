<?php

namespace Laurent\PiaBundle\Migrations\oci8;

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
                id NUMBER(10) NOT NULL, 
                name VARCHAR2(255) NOT NULL, 
                description CLOB NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            DECLARE constraints_Count NUMBER; BEGIN 
            SELECT COUNT(CONSTRAINT_NAME) INTO constraints_Count 
            FROM USER_CONSTRAINTS 
            WHERE TABLE_NAME = 'LAURENT_PIA_ACTION' 
            AND CONSTRAINT_TYPE = 'P'; IF constraints_Count = 0 
            OR constraints_Count = '' THEN EXECUTE IMMEDIATE 'ALTER TABLE LAURENT_PIA_ACTION ADD CONSTRAINT LAURENT_PIA_ACTION_AI_PK PRIMARY KEY (ID)'; END IF; END;
        ");
        $this->addSql("
            CREATE SEQUENCE LAURENT_PIA_ACTION_ID_SEQ START WITH 1 MINVALUE 1 INCREMENT BY 1
        ");
        $this->addSql("
            CREATE TRIGGER LAURENT_PIA_ACTION_AI_PK BEFORE INSERT ON LAURENT_PIA_ACTION FOR EACH ROW DECLARE last_Sequence NUMBER; last_InsertID NUMBER; BEGIN 
            SELECT LAURENT_PIA_ACTION_ID_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; IF (
                : NEW.ID IS NULL 
                OR : NEW.ID = 0
            ) THEN 
            SELECT LAURENT_PIA_ACTION_ID_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; ELSE 
            SELECT NVL(Last_Number, 0) INTO last_Sequence 
            FROM User_Sequences 
            WHERE Sequence_Name = 'LAURENT_PIA_ACTION_ID_SEQ'; 
            SELECT : NEW.ID INTO last_InsertID 
            FROM DUAL; WHILE (last_InsertID > last_Sequence) LOOP 
            SELECT LAURENT_PIA_ACTION_ID_SEQ.NEXTVAL INTO last_Sequence 
            FROM DUAL; END LOOP; END IF; END;
        ");
        $this->addSql("
            CREATE TABLE laurent_pia_action_user (
                actions_id NUMBER(10) NOT NULL, 
                user_id NUMBER(10) NOT NULL, 
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
                id NUMBER(10) NOT NULL, 
                intervenant_id NUMBER(10) DEFAULT NULL, 
                taches_id NUMBER(10) DEFAULT NULL, 
                description CLOB NOT NULL, 
                \"date\" TIMESTAMP(0) DEFAULT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            DECLARE constraints_Count NUMBER; BEGIN 
            SELECT COUNT(CONSTRAINT_NAME) INTO constraints_Count 
            FROM USER_CONSTRAINTS 
            WHERE TABLE_NAME = 'LAURENT_PIA_SUIVI' 
            AND CONSTRAINT_TYPE = 'P'; IF constraints_Count = 0 
            OR constraints_Count = '' THEN EXECUTE IMMEDIATE 'ALTER TABLE LAURENT_PIA_SUIVI ADD CONSTRAINT LAURENT_PIA_SUIVI_AI_PK PRIMARY KEY (ID)'; END IF; END;
        ");
        $this->addSql("
            CREATE SEQUENCE LAURENT_PIA_SUIVI_ID_SEQ START WITH 1 MINVALUE 1 INCREMENT BY 1
        ");
        $this->addSql("
            CREATE TRIGGER LAURENT_PIA_SUIVI_AI_PK BEFORE INSERT ON LAURENT_PIA_SUIVI FOR EACH ROW DECLARE last_Sequence NUMBER; last_InsertID NUMBER; BEGIN 
            SELECT LAURENT_PIA_SUIVI_ID_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; IF (
                : NEW.ID IS NULL 
                OR : NEW.ID = 0
            ) THEN 
            SELECT LAURENT_PIA_SUIVI_ID_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; ELSE 
            SELECT NVL(Last_Number, 0) INTO last_Sequence 
            FROM User_Sequences 
            WHERE Sequence_Name = 'LAURENT_PIA_SUIVI_ID_SEQ'; 
            SELECT : NEW.ID INTO last_InsertID 
            FROM DUAL; WHILE (last_InsertID > last_Sequence) LOOP 
            SELECT LAURENT_PIA_SUIVI_ID_SEQ.NEXTVAL INTO last_Sequence 
            FROM DUAL; END LOOP; END IF; END;
        ");
        $this->addSql("
            CREATE INDEX IDX_553A5370AB9A1716 ON laurent_pia_suivi (intervenant_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_553A5370B8A61670 ON laurent_pia_suivi (taches_id)
        ");
        $this->addSql("
            CREATE TABLE laurent_pia_taches (
                id NUMBER(10) NOT NULL, 
                action_id NUMBER(10) DEFAULT NULL, 
                responsable_id NUMBER(10) DEFAULT NULL, 
                eleves_id NUMBER(10) DEFAULT NULL, 
                titre VARCHAR2(255) DEFAULT NULL, 
                commentaire CLOB DEFAULT NULL, 
                fini NUMBER(1) DEFAULT '0' NOT NULL, 
                priorite NUMBER(10) DEFAULT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            DECLARE constraints_Count NUMBER; BEGIN 
            SELECT COUNT(CONSTRAINT_NAME) INTO constraints_Count 
            FROM USER_CONSTRAINTS 
            WHERE TABLE_NAME = 'LAURENT_PIA_TACHES' 
            AND CONSTRAINT_TYPE = 'P'; IF constraints_Count = 0 
            OR constraints_Count = '' THEN EXECUTE IMMEDIATE 'ALTER TABLE LAURENT_PIA_TACHES ADD CONSTRAINT LAURENT_PIA_TACHES_AI_PK PRIMARY KEY (ID)'; END IF; END;
        ");
        $this->addSql("
            CREATE SEQUENCE LAURENT_PIA_TACHES_ID_SEQ START WITH 1 MINVALUE 1 INCREMENT BY 1
        ");
        $this->addSql("
            CREATE TRIGGER LAURENT_PIA_TACHES_AI_PK BEFORE INSERT ON LAURENT_PIA_TACHES FOR EACH ROW DECLARE last_Sequence NUMBER; last_InsertID NUMBER; BEGIN 
            SELECT LAURENT_PIA_TACHES_ID_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; IF (
                : NEW.ID IS NULL 
                OR : NEW.ID = 0
            ) THEN 
            SELECT LAURENT_PIA_TACHES_ID_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; ELSE 
            SELECT NVL(Last_Number, 0) INTO last_Sequence 
            FROM User_Sequences 
            WHERE Sequence_Name = 'LAURENT_PIA_TACHES_ID_SEQ'; 
            SELECT : NEW.ID INTO last_InsertID 
            FROM DUAL; WHILE (last_InsertID > last_Sequence) LOOP 
            SELECT LAURENT_PIA_TACHES_ID_SEQ.NEXTVAL INTO last_Sequence 
            FROM DUAL; END LOOP; END IF; END;
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
                taches_id NUMBER(10) NOT NULL, 
                user_id NUMBER(10) NOT NULL, 
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