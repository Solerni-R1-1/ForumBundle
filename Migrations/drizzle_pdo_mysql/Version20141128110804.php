<?php

namespace Claroline\ForumBundle\Migrations\drizzle_pdo_mysql;

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
            ADD last_editor_id INT DEFAULT NULL
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
            DROP FOREIGN KEY FK_6A49AC0E7E5A734A
        ");
        $this->addSql("
            DROP INDEX IDX_6A49AC0E7E5A734A ON claro_forum_message
        ");
        $this->addSql("
            ALTER TABLE claro_forum_message 
            DROP last_editor_id
        ");
    }
}