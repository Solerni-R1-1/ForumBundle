<?php

namespace Claroline\ForumBundle\Migrations\sqlsrv;

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
                id INT IDENTITY NOT NULL, 
                message_id INT, 
                forum_id INT, 
                category_id INT, 
                user_id INT, 
                PRIMARY KEY (id)
            )
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_7C55CA4B537A1329 ON claro_forum_last_message (message_id) 
            WHERE message_id IS NOT NULL
        ");
        $this->addSql("
            CREATE INDEX IDX_7C55CA4B29CCBAD0 ON claro_forum_last_message (forum_id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_7C55CA4B12469DE2 ON claro_forum_last_message (category_id) 
            WHERE category_id IS NOT NULL
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_7C55CA4BA76ED395 ON claro_forum_last_message (user_id) 
            WHERE user_id IS NOT NULL
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