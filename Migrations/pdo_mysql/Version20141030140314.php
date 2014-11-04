<?php

namespace Claroline\ForumBundle\Migrations\pdo_mysql;

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
                INDEX IDX_7C55CA4B12469DE2 (category_id), 
                INDEX IDX_7C55CA4BA76ED395 (user_id), 
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

		$this->addSql("
			INSERT INTO claro_forum_last_message
				(SELECT
					'' AS id,
					c0_.id AS message_id,
					c4_.id AS forum_id,
					c1_.id AS category_id,
					c2_.id AS user_id
				FROM claro_forum_message c0_
					INNER JOIN claro_user c2_
						ON c0_.user_id = c2_.id
					INNER JOIN claro_forum_subject c3_
						ON c0_.subject_id = c3_.id
					INNER JOIN claro_forum_category c1_
						ON c3_.category_id = c1_.id
					INNER JOIN claro_forum c4_
						ON c1_.forum_id = c4_.id
				WHERE
					c0_.id IN (
						SELECT
							max(c5_.id) AS dctrn__1
						FROM claro_forum_message c5_
							INNER JOIN claro_forum_subject c6_
								ON c5_.subject_id = c6_.id
							INNER JOIN claro_forum_category c7_
								ON c6_.category_id = c7_.id
						WHERE
							c7_.id = c1_.id
						GROUP BY c1_.id )
				GROUP BY c1_.id);
		");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP TABLE claro_forum_last_message
        ");
    }
}
