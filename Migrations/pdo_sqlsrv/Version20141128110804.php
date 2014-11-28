<?php

namespace Claroline\ForumBundle\Migrations\pdo_sqlsrv;

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
            ALTER TABLE claro_forum_message 
            ADD last_editor_id INT
        ");
        $this->addSql("
            ALTER TABLE claro_forum_message 
            ADD CONSTRAINT FK_6A49AC0E7E5A734A FOREIGN KEY (last_editor_id) 
            REFERENCES claro_user (id)
        ");
        $this->addSql("
            CREATE INDEX IDX_6A49AC0E7E5A734A ON claro_forum_message (last_editor_id)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_forum_message 
            DROP COLUMN last_editor_id
        ");
        $this->addSql("
            ALTER TABLE claro_forum_message 
            DROP CONSTRAINT FK_6A49AC0E7E5A734A
        ");
        $this->addSql("
            IF EXISTS (
                SELECT * 
                FROM sysobjects 
                WHERE name = 'IDX_6A49AC0E7E5A734A'
            ) 
            ALTER TABLE claro_forum_message 
            DROP CONSTRAINT IDX_6A49AC0E7E5A734A ELSE 
            DROP INDEX IDX_6A49AC0E7E5A734A ON claro_forum_message
        ");
    }
}