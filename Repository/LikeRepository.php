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
use Claroline\ForumBundle\Entity\Message;
use Claroline\CoreBundle\Entity\User;

class LikeRepository extends EntityRepository {

	public function getLikes( Message $message, $weight ) {
        
        
        if ( $weight !== -1 && $weight !== 0 && $weight !== 1 ) {
            $dql = "
                SELECT l FROM Claroline\ForumBundle\Entity\Like l
                WHERE l.message = {$message->getId()}";
        } else {
            $dql = "
                SELECT l FROM Claroline\ForumBundle\Entity\Like l
                WHERE l.message = {$message->getId()}
                AND l.weight = {$weight}"; 
        }

        $query = $this->_em->createQuery($dql);

        return $query->getResult();
        
    }
    
    public function getUserLike( Message $message, User $user ) {
        
        $dql = "
                SELECT l FROM Claroline\ForumBundle\Entity\Like l
                WHERE l.message = {$message->getId()}
                AND l.user = {$user->getId()}";

        $query = $this->_em->createQuery($dql);

        return $query->getOneOrNullResult();
        
    }
    
}
