<?php

namespace Claroline\ForumBundle\Migrations\pdo_sqlite;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/11/25 04:34:30
 */
class Version20141125163429 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_forum_category 
            ADD COLUMN isUserLocked BOOLEAN NOT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP INDEX IDX_2192ACF729CCBAD0
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_forum_category AS 
            SELECT id, 
            forum_id, 
            created, 
            modificationDate, 
            name 
            FROM claro_forum_category
        ");
        $this->addSql("
            DROP TABLE claro_forum_category
        ");
        $this->addSql("
            CREATE TABLE claro_forum_category (
                id INTEGER NOT NULL, 
                forum_id INTEGER DEFAULT NULL, 
                created DATETIME NOT NULL, 
                modificationDate DATETIME NOT NULL, 
                name VARCHAR(255) NOT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_2192ACF729CCBAD0 FOREIGN KEY (forum_id) 
                REFERENCES claro_forum (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_forum_category (
                id, forum_id, created, modificationDate, 
                name
            ) 
            SELECT id, 
            forum_id, 
            created, 
            modificationDate, 
            name 
            FROM __temp__claro_forum_category
        ");
        $this->addSql("
            DROP TABLE __temp__claro_forum_category
        ");
        $this->addSql("
            CREATE INDEX IDX_2192ACF729CCBAD0 ON claro_forum_category (forum_id)
        ");
    }
}