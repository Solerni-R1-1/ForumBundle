<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\ForumBundle\Repository;

use Claroline\ForumBundle\Entity\Forum;
use Claroline\ForumBundle\Entity\Subject;
use Claroline\ForumBundle\Entity\Category;
use Doctrine\ORM\EntityRepository;

class ForumRepository extends EntityRepository
{
      /**
       * Deep magic goes here.
       * Gets a subject with some of its last messages datas.
       *
       * @param ResourceInstance $forum
       * @return type
       */
    public function findSubjects(Category $category, $getQuery = false)
    {
        $dql = "
            SELECT s.id as id,
            COUNT(m_count.id) AS count_messages,
            MAX(m.creationDate) AS last_message_created,
            s.id as subjectId,
            s.title as title,
            s.isSticked as isSticked,
            subjectCreator.lastName as subject_creator_lastname,
            subjectCreator.firstName as subject_creator_firstname,
            subjectCreator.id as subject_creator_id,
            subjectCreator.publicUrl as subject_creator_public_url,
            lastUser.lastName as last_message_creator_lastname,
            lastUser.firstName as last_message_creator_firstname,
            lastUser.id as last_message_creator_id,
            lastUser.publicUrl as last_message_creator_public_url,
            s.creationDate as subject_created
            FROM Claroline\ForumBundle\Entity\Subject s
            JOIN s.messages m_count
            JOIN s.creator subjectCreator
            JOIN s.category category
            JOIN s.messages m
            JOIN m.creator lastUser WITH lastUser.id =
                (
                    SELECT lcu.id FROM Claroline\ForumBundle\Entity\Message m2
                    JOIN m2.subject s2
                    JOIN m2.creator lcu
                    JOIN s2.category c2
                    WHERE NOT EXISTS
                    (
                        SELECT m3 FROM Claroline\ForumBundle\Entity\Message m3
                        JOIN m3.subject s3
                        WHERE s2.id = s3.id
                        AND m2.id < m3.id
                    )
                    and c2.id = :categoryId
                    and m2.id = m.id
                )
            WHERE category.id = :categoryId
            GROUP BY s.id, subjectCreator.lastName, subjectCreator.firstName, lastUser.lastName, lastUser.firstName
            ORDER BY isSticked DESC, last_message_created DESC
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('categoryId', $category->getId());

        return ($getQuery) ? $query: $query->getResult();
    }

    public function findCategories(Forum $forum, $getQuery = false)
    {
        $dql = "
            SELECT c.id as id,
            count(m) as count_messages,
            c.name as name,
            c.creationDate as creation_date
            FROM Claroline\ForumBundle\Entity\Category c
            LEFT JOIN c.subjects s
            LEFT JOIN s.messages m
            JOIN c.forum forum
            WHERE forum.id = :forumId
            GROUP BY c.id
        ";

        $query = $this->_em->createQuery($dql);
        $query->setParameter('forumId', $forum->getId());

        $categories = $query->getResult();

        $dql = "SELECT
	        		m.creationDate as last_message_created,
		            c.id as categoryId,
		            lastUser.lastName as last_message_creator_lastname,
		            lastUser.firstName as last_message_creator_firstname,
		            lastUser.id as last_message_creator_id,
		            lastUser.publicUrl as last_message_creator_public_url 
        		FROM Claroline\ForumBundle\Entity\LastMessage lm
        		JOIN lm.message m
        		JOIN lm.category c
        		JOIN lm.user lastUser
        		
        		WHERE lm.forum = :forum
        		GROUP BY lm.category";
        
        $query = $this->_em->createQuery($dql);
        $query->setParameter('forum', $forum);
        $lastMessages = $query->getResult();
        
        //merge both results
        $merged = $categories;

        foreach ($categories as $key => $category) {

            $merged[$key]['last_message_created'] = null;
            $merged[$key]['last_message_creator_lastname'] = null;
            $merged[$key]['last_message_creator_firstname'] = null;
            $merged[$key]['last_message_creator_id'] = null;
            $merged[$key]['last_message_creator_public_url'] = null;
            foreach ($lastMessages as $lastMessage) {
                if ($category['id'] === $lastMessage['categoryId']) {
                    $merged[$key]['last_message_created'] = $lastMessage['last_message_created'];
                    $merged[$key]['last_message_creator_lastname'] = $lastMessage['last_message_creator_lastname'];
                    $merged[$key]['last_message_creator_firstname'] = $lastMessage['last_message_creator_firstname'];
                    $merged[$key]['last_message_creator_id'] = $lastMessage['last_message_creator_id'];
                    $merged[$key]['last_message_creator_public_url'] = $lastMessage['last_message_creator_public_url'];
                }
            }
        }

        return $merged;
    }

    public function countMessagesForSubject(Subject $subject)
    {
        $dql = "
            SELECT Count(m) FROM Claroline\ForumBundle\Entity\Message m
            JOIN m.subject s
            WHERE s.id = :subjectId";

        $query = $this->_em->createQuery($dql);
        $query->setParameter('subjectId', $subject->getId());

        return $query->getSingleScalarResult();
    }

    public function countSubjectsForForum($forum)
    {
        $dql = "
            SELECT COUNT(s) FROM Claroline\ForumBundle\Entity\Subject s
            JOIN s.forum p
            WHERE p.id = :forumId";

        $query = $this->_em->createQuery($dql);
        $query->setParameter('forumId', $forum->getId());

        return $query->getSingleScalarResult();
    }

    public function search(Forum $forum, $content, $getQuery = true)
    {
        $dql = "SELECT m FROM Claroline\ForumBundle\Entity\Message m
            JOIN m.subject s
            JOIN s.category c
            JOIN c.forum f
            WHERE m.content LIKE :content
            and f.id = {$forum->getId()}
        ";

        $query = $this->_em->createQuery($dql);
        $query->setParameter('content', '%'.$content.'%');

        return ($getQuery) ? $query: $query->getResult();
    }
    
    public function getForumAssociatedToSession($sessionId) {
    	$dql = "SELECT f FROM Claroline\ForumBundle\Entity\Forum f
    			JOIN f.resourceNode rn
    			JOIN Claroline\CoreBundle\Entity\Mooc\MoocSession s
    			WITH 1 = 1
    			WHERE s.forum = rn.id
    			AND s.id = {$sessionId}";
    	
    	
    	$query = $this->_em->createQuery($dql);
    	
    	$result = $query->getResult();
    	
    	return (count($result) > 0 ? $result[0] : NULL);
    }
    
    public function getForumFromResourceNode($node) {
    	$dql = "SELECT f FROM Claroline\ForumBundle\Entity\Forum f
    			JOIN f.resourceNode rn
    			WHERE rn = :node";
    	$query = $this->_em->createQuery($dql);
    	$query->setParameter('node', $node);
    	
    	$result = $query->getResult();
    	
    	return (count($result) > 0 ? $result[0] : NULL);
    }
}
