<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\ForumBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Claroline\ForumBundle\Entity\Message;
use Claroline\CoreBundle\Entity\User;
use Gedmo\Mapping\Annotation as Gedmo;


/**
 * @ORM\Entity
 * @ORM\Table(name="claro_forum_like")
 * @ORM\Entity(repositoryClass="Claroline\ForumBundle\Repository\LikeRepository")
 */
class Like
{

    /**
     * @ORM\Id
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\ForumBundle\Entity\Message",
     *     inversedBy="likes"
     * )
     */
    protected $message;

    /**
     * @ORM\Id
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\User"
     * )
     */
    protected $user;
    
    /**
     * @ORM\Column(type="integer")
     */
    protected $weight;

    /**
     * Set message
     *
     * @param \Claroline\ForumBundle\Entity\Message $message
     * @return Like
     */
    public function setMessage(\Claroline\ForumBundle\Entity\Message $message = null)
    {
        $this->message = $message;

        return $this;
    }

    /**
     * Get message
     *
     * @return \Claroline\ForumBundle\Entity\Message 
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Set user
     *
     * @param \Claroline\CoreBundle\Entity\User $user
     * @return Like
     */
    public function setUser(\Claroline\CoreBundle\Entity\User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \Claroline\CoreBundle\Entity\User 
     */
    public function getUser()
    {
        return $this->user;
    }
    
    public function getWeight() {
        return $this->weight;
    }

    public function setWeight($weight) {
        $this->weight = $weight;
        
        return $this;
    }


}
