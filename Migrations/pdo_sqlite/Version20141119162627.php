<?php

namespace Claroline\ForumBundle\Migrations\pdo_sqlite;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/11/19 04:26:28
 */
class Version20141119162627 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE claro_forum_like (
                message_id INTEGER NOT NULL, 
                user_id INTEGER NOT NULL, 
                weight INTEGER NOT NULL, 
                PRIMARY KEY(message_id, user_id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_D0B6B3E537A1329 ON claro_forum_like (message_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_D0B6B3EA76ED395 ON claro_forum_like (user_id)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP TABLE claro_forum_like
        ");
    }
}