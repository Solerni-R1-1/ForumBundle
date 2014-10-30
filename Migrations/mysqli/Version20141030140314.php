<?php

namespace Claroline\ForumBundle\Migrations\mysqli;

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
                id INT AUTO_INCREMENT NOT NULL, 
                message_id INT DEFAULT NULL, 
                forum_id INT DEFAULT NULL, 
                category_id INT DEFAULT NULL, 
                user_id INT DEFAULT NULL, 
                UNIQUE INDEX UNIQ_7C55CA4B537A1329 (message_id), 
                INDEX IDX_7C55CA4B29CCBAD0 (forum_id), 
                UNIQUE INDEX UNIQ_7C55CA4B12469DE2 (category_id), 
                UNIQUE INDEX UNIQ_7C55CA4BA76ED395 (user_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
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