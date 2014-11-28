<?php

namespace Claroline\ForumBundle\Migrations\pdo_sqlite;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/11/28 11:08:06
 */
class Version20141128110804 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            DROP INDEX IDX_6A49AC0E23EDC87
        ");
        $this->addSql("
            DROP INDEX IDX_6A49AC0EA76ED395
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_forum_message AS 
            SELECT id, 
            subject_id, 
            user_id, 
            content, 
            created, 
            updated 
            FROM claro_forum_message
        ");
        $this->addSql("
            DROP TABLE claro_forum_message
        ");
        $this->addSql("
            CREATE TABLE claro_forum_message (
                id INTEGER NOT NULL, 
                subject_id INTEGER DEFAULT NULL, 
                user_id INTEGER DEFAULT NULL, 
                last_editor_id INTEGER DEFAULT NULL, 
                content CLOB NOT NULL, 
                created DATETIME NOT NULL, 
                updated DATETIME NOT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_6A49AC0E23EDC87 FOREIGN KEY (subject_id) 
                REFERENCES claro_forum_subject (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_6A49AC0EA76ED395 FOREIGN KEY (user_id) 
                REFERENCES claro_user (id) NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_6A49AC0E7E5A734A FOREIGN KEY (last_editor_id) 
                REFERENCES claro_user (id) NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_forum_message (
                id, subject_id, user_id, content, created, 
                updated
            ) 
            SELECT id, 
            subject_id, 
            user_id, 
            content, 
            created, 
            updated 
            FROM __temp__claro_forum_message
        ");
        $this->addSql("
            DROP TABLE __temp__claro_forum_message
        ");
        $this->addSql("
            CREATE INDEX IDX_6A49AC0E23EDC87 ON claro_forum_message (subject_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_6A49AC0EA76ED395 ON claro_forum_message (user_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_6A49AC0E7E5A734A ON claro_forum_message (last_editor_id)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP INDEX IDX_6A49AC0E23EDC87
        ");
        $this->addSql("
            DROP INDEX IDX_6A49AC0EA76ED395
        ");
        $this->addSql("
            DROP INDEX IDX_6A49AC0E7E5A734A
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_forum_message AS 
            SELECT id, 
            subject_id, 
            user_id, 
            content, 
            created, 
            updated 
            FROM claro_forum_message
        ");
        $this->addSql("
            DROP TABLE claro_forum_message
        ");
        $this->addSql("
            CREATE TABLE claro_forum_message (
                id INTEGER NOT NULL, 
                subject_id INTEGER DEFAULT NULL, 
                user_id INTEGER DEFAULT NULL, 
                content CLOB NOT NULL, 
                created DATETIME NOT NULL, 
                updated DATETIME NOT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_6A49AC0E23EDC87 FOREIGN KEY (subject_id) 
                REFERENCES claro_forum_subject (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_6A49AC0EA76ED395 FOREIGN KEY (user_id) 
                REFERENCES claro_user (id) NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_forum_message (
                id, subject_id, user_id, content, created, 
                updated
            ) 
            SELECT id, 
            subject_id, 
            user_id, 
            content, 
            created, 
            updated 
            FROM __temp__claro_forum_message
        ");
        $this->addSql("
            DROP TABLE __temp__claro_forum_message
        ");
        $this->addSql("
            CREATE INDEX IDX_6A49AC0E23EDC87 ON claro_forum_message (subject_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_6A49AC0EA76ED395 ON claro_forum_message (user_id)
        ");
    }
}