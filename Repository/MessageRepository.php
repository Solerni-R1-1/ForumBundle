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

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Claroline\ForumBundle\Entity\Subject;
use Claroline\ForumBundle\Entity\Forum;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\User;
use ClassesWithParents\A;
use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;
use Claroline\CoreBundle\Entity\Mooc\MoocSession;
use Claroline\ForumBundle\Entity\Message;
use Claroline\ForumBundle\Entity\Category;

class MessageRepository extends EntityRepository
{
    public function findBySubject($subjectId, $userId, $getQuery = false, $order = "ASC")
    {
        
        // Order options
        if ( $order == 'POP'  ) {
            $order = 'nbLikes DESC';
        } else {
            $order = 'm.id ' . $order;
        }
        
        $dql = "
            SELECT m AS message,
                    u AS creator,
                    COUNT(DISTINCT l.user) AS nbLikes,
                    l2.weight AS hasVoted,
                    COUNT(DISTINCT m2) AS nbCreatorMessages
            FROM Claroline\ForumBundle\Entity\Message m
            JOIN m.creator u
            JOIN m.subject subject
            JOIN Claroline\ForumBundle\Entity\Message m2
                WITH m2.creator = u
            LEFT JOIN m.likes l
                WITH l.weight > 0
            LEFT JOIN m.likes l2
                WITH l2.user = {$userId}

            WHERE subject.id = {$subjectId}
            GROUP BY m.id
            ORDER BY {$order}
        ";
        
        $query = $this->_em->createQuery($dql);

        return ($getQuery) ? $query: $query->getResult();
    }


    public function countNbMessagesInForum(ResourceNode $forumNode, array $roles = array())
    {
    	$dql = "
	    	SELECT COUNT(DISTINCT m) FROM Claroline\ForumBundle\Entity\Message m
	    	JOIN m.subject s
	    	JOIN s.category c
	    	JOIN c.forum f
    		JOIN f.resourceNode rn
    		JOIN m.creator u
	    	WHERE rn = :forum
    		AND u NOT IN (
    			SELECT u2.id FROM Claroline\CoreBundle\Entity\User u2
    			JOIN u2.roles r
    			WHERE r.name IN (:roles))";
    
    
    	$query = $this->_em->createQuery($dql);
    	$query->setParameter("forum", $forumNode);
    	$query->setParameter("roles", $roles);
    
    	return $query->getSingleScalarResult();
    }
    


    public function countNbMessagesInForumGroupBySubjectSince(ResourceNode $forumNode, \DateTime $since, $excludeRoles = null)
    {
    	$parameters = array(
    			"since" => $since,
    			"forum" => $forumNode,
    			"roles" => $excludeRoles
    	);
    	$dql = "
	    	SELECT s.title as subject, count(m) as nbMessages FROM Claroline\ForumBundle\Entity\Subject s
	    	JOIN s.messages m
	    	JOIN s.category c
	    	JOIN c.forum f
    		JOIN f.resourceNode rn
	    	WHERE rn = :forum
    			AND m.creationDate >= :since
    			AND m.creator NOT IN (
    				SELECT u FROM Claroline\CoreBundle\Entity\User u
    				JOIN u.roles as r
		   			WHERE r.name IN (:roles))
    		GROUP BY s
    		ORDER BY nbMessages DESC";
    
    
    	$query = $this->_em->createQuery($dql);
    	$query->setParameters($parameters);
    
    	return $query->getResult();
    }
    

    public function getAllMessagesByForum(ResourceNode $forumNode)
    {
    	$dql = "
	    	SELECT m FROM Claroline\ForumBundle\Entity\Message m
	    	JOIN m.subject s
	    	JOIN s.category c
	    	JOIN c.forum f
    		JOIN f.resourceNode rn
	    	WHERE rn = :forum";
    	 
    	$query = $this->_em->createQuery($dql);
    	$query->setParameter("forum", $forumNode);
    
    	return $query->getResult();
    }
    
    public function findInitialBySubject($subjectId)
    {
        $dql = "SELECT m FROM  Claroline\ForumBundle\Entity\Message m
                WHERE m.id IN (SELECT min(m_1.id) FROM  Claroline\ForumBundle\Entity\Message m_1
                    JOIN m_1.subject s_2
                    WHERE s_2 = {$subjectId})
                ";

        $query = $this->_em->createQuery($dql);

        return $query->getSingleResult();
    }
    
    public function findNLastByForum(array $workspaces, array $roles, $n)
    {
        
        $dql = "SELECT m FROM Claroline\ForumBundle\Entity\Message m
                JOIN m.subject s
                JOIN s.category c
                JOIN c.forum f
                JOIN f.resourceNode n
                JOIN n.workspace w
                WHERE w IN (:workspaces)
                ORDER BY m.creationDate DESC
                ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('workspaces', $workspaces);
        $query->setFirstResult(0)->setMaxResults($n);
        $paginator = new Paginator($query, $fetchJoinCollection = false);

        return $paginator;
    }
    
   public function findNLastBySession($session, array $roles, $n)
    {
    
    	$dql = "SELECT m FROM Claroline\ForumBundle\Entity\Message m
                JOIN m.subject s
                JOIN s.category c
                JOIN c.forum f
                JOIN f.resourceNode n
    			JOIN Claroline\CoreBundle\Entity\Mooc\MoocSession ms
    			WITH ms.forum = n
                WHERE :session = ms.id
                ORDER BY m.creationDate DESC
                ";
    	$query = $this->_em->createQuery($dql);
    	$query->setParameters(array(
    			'session' => $session->getId()
    	));
    	$query->setFirstResult(0)->setMaxResults($n);
    	$paginator = new Paginator($query, $fetchJoinCollection = false);
    
    	return $paginator;
    }
    
    public function findNLast($forum, array $roles, $n)
    {
    
    	$dql = "SELECT m FROM Claroline\ForumBundle\Entity\Message m
                JOIN m.subject s
                JOIN s.category c
                JOIN c.forum f
                WHERE f = :forum
                ORDER BY m.creationDate DESC
                ";
    	$query = $this->_em->createQuery($dql);
    	$query->setParameters(array(
    			'forum' => $forum
    	));
    	$query->setFirstResult(0)->setMaxResults($n);
    	$paginator = new Paginator($query, $fetchJoinCollection = false);
    
    	return $paginator;
    }
    
    public function findOneFromLastInCategory(Category $category, $offset = 0, $excludeSubject = null )
    {
    
        $parameters = array('category' => $category );
        
        if ( $excludeSubject  ) {
            $dql = "SELECT m FROM Claroline\ForumBundle\Entity\Message m
                JOIN m.subject s
                    WITH s != :subject
                JOIN s.category c
                    WITH c = :category
                ORDER BY m.creationDate DESC
                ";
            $parameters['subject'] =  $excludeSubject;
        } else {
            $dql = "SELECT m FROM Claroline\ForumBundle\Entity\Message m
                JOIN m.subject s
                JOIN s.category c
                    WITH c = :category
                ORDER BY m.creationDate DESC
                ";
        }
        
    	$query = $this->_em->createQuery($dql);
        $query->setFirstResult($offset)->setMaxResults(1);
    	$query->setParameters($parameters);
    
    	return $query->getOneOrNullResult();
    }
    
    public function findAllPublicationsBetween(ResourceNode $forum, \DateTime $from, \DateTime $to, $excludeRoles = null) {
    	$parameters = array(
    			"forum" => $forum,
    			"from" => $from,
    			"to" => $to
    	);
    	if ($excludeRoles != null) {
    		$parameters['roles'] = $excludeRoles;
    		$dql = "SELECT m FROM Claroline\ForumBundle\Entity\Message m
	                JOIN m.subject s
	                JOIN s.category c
	                JOIN c.forum f
	    			JOIN f.resourceNode rn
    				JOIN m.creator u
	                WHERE rn = :forum 
    				AND m.creationDate < :to
    				AND m.creationDate > :from
    				AND u NOT IN (
    					SELECT u2.id FROM Claroline\CoreBundle\Entity\User u2
		    			JOIN u2.roles r
		    			WHERE r.name IN (:roles))
	                ORDER BY m.creationDate DESC";
    	} else {
	    	$dql = "SELECT m FROM Claroline\ForumBundle\Entity\Message m
	                JOIN m.subject s
	                JOIN s.category c
	                JOIN c.forum f
	    			JOIN f.resourceNode rn
	                WHERE
	    				rn = :forum AND
	    				m.creationDate < :to AND
	    				m.creationDate > :from 
	                ORDER BY m.creationDate DESC";
    	}
    	$query = $this->_em->createQuery($dql);
    	$query->setParameters($parameters);
    	
    	return $query->getResult();
    }
    
    public function countUserMessages(User $user) {
    	$dql = "SELECT count(m)
    			FROM Claroline\ForumBundle\Entity\Message m
    			WHERE m.creator = :user";
    	
    	$query = $this->_em->createQuery($dql);
    	$query->setParameter("user", $user);
    	
    	return $query->getSingleScalarResult();
    }
    
    public function countMessagesForUser(ResourceNode $forum, User $user, \DateTime $from = null, \DateTime $to = null) {
    	$dql = "SELECT count(m) FROM Claroline\ForumBundle\Entity\Message m
                JOIN m.subject s
                JOIN s.category c
                JOIN c.forum f
    			JOIN f.resourceNode rn
                WHERE rn = :forum
    			AND m.creator = :user";
    	if ($from != null) {
    		$dql = $dql." AND m.creationDate > :from";
    	}
    	if ($to != null) {
    		$dql = $dql." AND m.creationDate < :to";
    	}
    	$query = $this->_em->createQuery($dql);
    	$query->setParameters(array(
    			"forum" => $forum,
    			"user" => $user
    	));
    	if ($from != null) {
    		$query->setParameter("from", $from);
    	}
    	if ($to != null) {
    		$query->setParameter("to", $to);
    	}
    	 
    	return $query->getSingleScalarResult();
    }
    
    public function getPreparationForUserAnalytics(ResourceNode $forum, $from, $to, $excludeRoles = array()) {
    	$dql = "SELECT u AS user,
    				SUBSTRING(m.creationDate, 1, 10) AS date,
    				COUNT(m) AS nbMessages
    			FROM Claroline\CoreBundle\Entity\User u
    			JOIN Claroline\ForumBundle\Entity\Message m
    				WITH m.creator = u
                JOIN m.subject s
                JOIN s.category c
                JOIN c.forum f
    			JOIN f.resourceNode rn
                WHERE rn = :forum
    			AND m.creationDate >= :from
    			AND m.creationDate <= :to
    			AND (m.creator NOT IN (
		   					SELECT u3 FROM Claroline\CoreBundle\Entity\User u3
		   					JOIN u3.roles as r2
		   					WHERE r2.name IN (:roles)))
    			GROUP BY date, m.creator";
    	
    	$parameters = array(
    			"from" 		=> $from,
    			"to" 		=> $to,
    			"forum" 	=> $forum,
    			"roles"		=> $excludeRoles
    	);
    
    	$query = $this->_em->createQuery($dql);
    	$query->setParameters($parameters);
    	 
    	return $query->getResult();
    }

    public function countUserMessagesLiked(User $user) {
    	$dql = "SELECT COUNT(DISTINCT m)
    			FROM Claroline\ForumBundle\Entity\Message m
    			JOIN m.likes l
    				WITH l.weight > 0
    			WHERE m.creator = :user";
    	 
    	$query = $this->_em->createQuery($dql);
    	$query->setParameter("user", $user);
    
    	return $query->getSingleScalarResult();
    }
    
    public function CountFromStartToMessage($message) {
        $dql = "SELECT COUNT(DISTINCT m)
    			FROM Claroline\ForumBundle\Entity\Message m
    			WHERE m.subject = {$message->getSubject()->getId()}
                AND m.id < {$message->getId()}";
    	 
    	$query = $this->_em->createQuery($dql);

    	return $query->getSingleScalarResult();
    }
}
