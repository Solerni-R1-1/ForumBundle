<?php

namespace Claroline\ForumBundle\Migrations\pdo_sqlite;

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
                id INTEGER NOT NULL, 
                message_id INTEGER DEFAULT NULL, 
                forum_id INTEGER DEFAULT NULL, 
                category_id INTEGER DEFAULT NULL, 
                user_id INTEGER DEFAULT NULL, 
                PRIMARY KEY(id)
            )
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
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP TABLE claro_forum_last_message
        ");
    }
}