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

class MessageRepository extends EntityRepository
{
    public function findBySubject(Subject $subject, $getQuery = false)
    {
        $dql = "
            SELECT m, u, pws FROM Claroline\ForumBundle\Entity\Message m
            JOIN m.creator u
            JOIN u.personalWorkspace pws
            JOIN m.subject subject
            WHERE subject.id = {$subject->getId()}";

        $query = $this->_em->createQuery($dql);

        return ($getQuery) ? $query: $query->getResult();
    }


    public function countNbMessagesInForum(ResourceNode $forumNode)
    {
    	$dql = "
	    	SELECT count(m) FROM Claroline\ForumBundle\Entity\Message m
	    	JOIN m.subject s
	    	JOIN s.category c
	    	JOIN c.forum f
    		JOIN f.resourceNode rn
	    	WHERE rn = :forum";
    
    
    	$query = $this->_em->createQuery($dql);
    	$query->setParameter("forum", $forumNode);
    
    	return $query->getSingleScalarResult();
    }
    


    public function countNbMessagesInForumGroupBySubjectSince(ResourceNode $forumNode, \DateTime $since)
    {
    	$dql = "
	    	SELECT count(m) as nbMessages, s as subject FROM Claroline\ForumBundle\Entity\Subject s
	    	JOIN s.messages m
	    	JOIN s.category c
	    	JOIN c.forum f
    		JOIN f.resourceNode rn
	    	WHERE rn = :forum
    			AND m.creationDate >= :since
    		GROUP BY s
    		ORDER BY nbMessages DESC";
    
    
    	$query = $this->_em->createQuery($dql);
    	$query->setParameters(
    			array(
    					"forum" => $forumNode,
    					"since" => $since
    			)
    	);
    
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
    
    public function findAllPublicationsBetween(ResourceNode $forum, \DateTime $from, \DateTime $to) {
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
    	$query = $this->_em->createQuery($dql);
    	$query->setParameters(array(
    			"forum" => $forum,
    			"from" => $from,
    			"to" => $to
    	));
    	
    	return $query->getResult();
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
}