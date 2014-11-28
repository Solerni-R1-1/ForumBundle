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
use Claroline\ForumBundle\Entity\Subject;
use Claroline\CoreBundle\Entity\User;
use Gedmo\Mapping\Annotation as Gedmo;
use Claroline\CoreBundle\Entity\Resource\AbstractIndexableResourceElement;

/**
 * @ORM\Entity
 * @ORM\Table(name="claro_forum_message")
 * @ORM\Entity(repositoryClass="Claroline\ForumBundle\Repository\MessageRepository")
 */
class Message extends AbstractIndexableResourceElement
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(name="content", type="text")
     * @Assert\NotBlank()
     */
    protected $content;

    /**
     * @ORM\Column(name="created", type="datetime")
     * @Gedmo\Timestampable(on="create")
     */
    protected $creationDate;

    /**
     * @ORM\Column(type="datetime")
     * @Gedmo\Timestampable(on="update")
     */
    protected $updated;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\ForumBundle\Entity\Subject",
     *     inversedBy="messages"
     * )
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    protected $subject;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\User",
     *     cascade={"persist"}
     * )
     * @ORM\JoinColumn(name="user_id")
     */
    protected $creator;
    
    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\User",
     *     cascade={"persist"}
     * )
     * @ORM\JoinColumn(name="last_editor_id")
     */
    protected $lastEditedBy;
    
    /**
     * @ORM\OneToMany(
     *     targetEntity="Claroline\ForumBundle\Entity\Like",
     *     mappedBy="message",
     *     cascade={"remove"}
     * )
     */
    protected $likes;

    /**
     * Returns the resource id.
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    public function setContent($content)
    {
        $this->content = $content;
    }

    public function getContent()
    {
        return $this->content;
    }

    public function setSubject(Subject $subject)
    {
        $this->subject = $subject;
    }

    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * Sets the message creator.
     *
     * @param \Claroline\CoreBundle\Entity\User
     */
    public function setCreator(User $creator)
    {
        $this->creator = $creator;
    }

    public function getCreator()
    {
        return $this->creator;
    }

    public function getCreationDate()
    {
        return $this->creationDate;
    }

    public function getUpdated()
    {
        return $this->updated;
    }

    public function setUpdated($updated)
    {
        $this->updated = $updated;
    }

    public function fillIndexableDocument(&$doc)
    {
        $doc = parent::fillIndexableDocument($doc);

        $doc->forum_id = $this->getSubject()->getCategory()->getForum()->getId();
        $doc->forum_name = $this->getSubject()->getCategory()->getForum()->getResourceNode()->getName();
        $doc->forum_category_id = $this->getSubject()->getCategory()->getId();
        $doc->forum_category_name = $this->getSubject()->getCategory()->getName();
        $doc->forum_category_url= $this->get('router')->generate('claro_forum_subjects', array(
            'category' => $doc->forum_category_id
        ));
        $doc->forum_subject_id = $this->getSubject()->getId();
        $doc->forum_subject_name = $this->getSubject()->getTitle();
        $doc->forum_subject_url= $this->get('router')->generate('claro_forum_messages', array(
            'subject' => $doc->forum_subject_id
        ));

        $doc->content_t = $this->getContent();

        $doc->forum_creator_id = $this->getCreator()->getId();
        $doc->forum_creator_name = $this->getCreator()->getFirstName().' '.$this->getCreator()->getLastName();
        $doc->forum_creator_profil_url =  $this->get('router')->generate('claro_public_profile_view', array(
            'publicUrl' => $this->getCreator()->getPublicUrl()
        ));

        $doc->forum_like = $this->getLikes();

        return $doc;
    }

    public function getResourceNode()
    {
        return $this->getSubject()->getCategory()->getForum()->getResourceNode();
    }
    
    public function getLikes() {
        return $this->likes;
    }
    
    public function getlastEditedBy() {
        return $this->lastEditedBy;
    }

    public function setlastEditedBy($lastEditedBy) {
        $this->lastEditedBy = $lastEditedBy;
    }



}
