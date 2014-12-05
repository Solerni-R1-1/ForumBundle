<?php

namespace Claroline\ForumBundle\Migrations\drizzle_pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/11/19 04:26:29
 */
class Version20141119162627 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE claro_forum_like (
                message_id INT NOT NULL, 
                user_id INT NOT NULL, 
                weight INT NOT NULL, 
                PRIMARY KEY(message_id, user_id), 
                INDEX IDX_D0B6B3E537A1329 (message_id), 
                INDEX IDX_D0B6B3EA76ED395 (user_id)
            )
        ");
        $this->addSql("
            ALTER TABLE claro_forum_like 
            ADD CONSTRAINT FK_D0B6B3E537A1329 FOREIGN KEY (message_id) 
            REFERENCES claro_forum_message (id)
        ");
        $this->addSql("
            ALTER TABLE claro_forum_like 
            ADD CONSTRAINT FK_D0B6B3EA76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP TABLE claro_forum_like
        ");
    }
}