<?php

namespace Claroline\ForumBundle\Migrations\pdo_oci;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/10/30 02:03:20
 */
class Version20141030140314 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE claro_forum_last_message (
                id NUMBER(10) NOT NULL, 
                message_id NUMBER(10) DEFAULT NULL, 
                forum_id NUMBER(10) DEFAULT NULL, 
                category_id NUMBER(10) DEFAULT NULL, 
                user_id NUMBER(10) DEFAULT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            DECLARE constraints_Count NUMBER; BEGIN 
            SELECT COUNT(CONSTRAINT_NAME) INTO constraints_Count 
            FROM USER_CONSTRAINTS 
            WHERE TABLE_NAME = 'CLARO_FORUM_LAST_MESSAGE' 
            AND CONSTRAINT_TYPE = 'P'; IF constraints_Count = 0 
            OR constraints_Count = '' THEN EXECUTE IMMEDIATE 'ALTER TABLE CLARO_FORUM_LAST_MESSAGE ADD CONSTRAINT CLARO_FORUM_LAST_MESSAGE_AI_PK PRIMARY KEY (ID)'; END IF; END;
        ");
        $this->addSql("
            CREATE SEQUENCE CLARO_FORUM_LAST_MESSAGE_ID_SEQ START WITH 1 MINVALUE 1 INCREMENT BY 1
        ");
        $this->addSql("
            CREATE TRIGGER CLARO_FORUM_LAST_MESSAGE_AI_PK BEFORE INSERT ON CLARO_FORUM_LAST_MESSAGE FOR EACH ROW DECLARE last_Sequence NUMBER; last_InsertID NUMBER; BEGIN 
            SELECT CLARO_FORUM_LAST_MESSAGE_ID_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; IF (
                : NEW.ID IS NULL 
                OR : NEW.ID = 0
            ) THEN 
            SELECT CLARO_FORUM_LAST_MESSAGE_ID_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; ELSE 
            SELECT NVL(Last_Number, 0) INTO last_Sequence 
            FROM User_Sequences 
            WHERE Sequence_Name = 'CLARO_FORUM_LAST_MESSAGE_ID_SEQ'; 
            SELECT : NEW.ID INTO last_InsertID 
            FROM DUAL; WHILE (last_InsertID > last_Sequence) LOOP 
            SELECT CLARO_FORUM_LAST_MESSAGE_ID_SEQ.NEXTVAL INTO last_Sequence 
            FROM DUAL; END LOOP; END IF; END;
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_7C55CA4B537A1329 ON claro_forum_last_message (message_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_7C55CA4B29CCBAD0 ON claro_forum_last_message (forum_id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_7C55CA4B12469DE2 ON claro_forum_last_message (category_id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_7C55CA4BA76ED395 ON claro_forum_last_message (user_id)
        ");
        $this->addSql("
            ALTER TABLE claro_forum_last_message 
            ADD CONSTRAINT FK_7C55CA4B537A1329 FOREIGN KEY (message_id) 
            REFERENCES claro_forum_message (id)
        ");
        $this->addSql("
            ALTER TABLE claro_forum_last_message 
            ADD CONSTRAINT FK_7C55CA4B29CCBAD0 FOREIGN KEY (forum_id) 
            REFERENCES claro_forum (id)
        ");
        $this->addSql("
            ALTER TABLE claro_forum_last_message 
            ADD CONSTRAINT FK_7C55CA4B12469DE2 FOREIGN KEY (category_id) 
            REFERENCES claro_forum_category (id)
        ");
        $this->addSql("
            ALTER TABLE claro_forum_last_message 
            ADD CONSTRAINT FK_7C55CA4BA76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP TABLE claro_forum_last_message
        ");
    }
}