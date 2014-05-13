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
use Claroline\CoreBundle\Entity\AbstractIndexable;

/**
 * @ORM\Entity
 * @ORM\Table(name="claro_forum_message")
 * @ORM\Entity(repositoryClass="Claroline\ForumBundle\Repository\MessageRepository")
 */
class Message extends AbstractIndexable
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
        $doc->forum_subject_id = $this->getSubject()->getId();
        $doc->forum_subject_name = $this->getSubject()->getTitle();
        $doc->content = $this->getContent();
        $resourceNode = $this->getSubject()->getCategory()->getForum()->getResourceNode();
        $doc->resource_id = $resourceNode->getId();
        $doc->wks_id = $resourceNode->getWorkspace()->getId();
        $doc->creation_date = $this->getCreationDate();
        $doc->modification_date = $this->getUpdated();
        $doc->type_name = $resourceNode->getResourceType()->getName() . '_message';
        $doc->owner_id = $resourceNode->getCreator()->getId();
        $doc->owner_name = $resourceNode->getCreator()->getFirstName() . ' ' .
                           $resourceNode->getCreator()->getLastName();

        return $doc;
    }

}
