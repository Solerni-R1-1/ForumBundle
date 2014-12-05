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
use Doctrine\Common\Collections\ArrayCollection;
use Claroline\CoreBundle\Entity\User;
use Gedmo\Mapping\Annotation as Gedmo;
use Claroline\CoreBundle\Entity\Resource\AbstractIndexableResourceElement;

/**
 * @ORM\Entity
 * @ORM\Table(name="claro_forum_subject")
 */
class Subject extends AbstractIndexableResourceElement
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column()
     * @Assert\NotBlank()
     */
    protected $title;

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
     *     targetEntity="Claroline\ForumBundle\Entity\Category",
     *     inversedBy="subjects"
     * )
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    protected $category;

    /**
     * @ORM\OneToMany(
     *     targetEntity="Claroline\ForumBundle\Entity\Message",
     *     mappedBy="subject", 
     *     cascade={"remove"}
     * )
     * @ORM\OrderBy({"id" = "ASC"})
     */
    protected $messages;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\User",
     *     cascade={"persist"}
     * )
     * @ORM\JoinColumn(name="user_id")
     */
    protected $creator;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $isSticked = false;
    
    /**
     * @ORM\Column(type="boolean")
     */
    protected $isUserLocked = false;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->messages = new ArrayCollection();
    }

    /**
     * Returns the resource id.
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function setTitle($title)
    {
        $this->title = $title;
    }

    public function setCategory(Category $category)
    {
        $this->category = $category;
        $category->addSubject($this);
    }

    public function getCategory()
    {
        return $this->category;
    }

    /**
     * Sets the subject creator.
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
    
    public function getUpdated() {
        return $this->updated;
    }
    
    public function getMessages()
    {
        return $this->messages;
    }

    public function setIsSticked($boolean)
    {
        $this->isSticked = $boolean;
    }

    public function isSticked()
    {
        return $this->isSticked;
    }
    
    public function fillIndexableDocument(&$doc)
    {
        $doc = parent::fillIndexableDocument($doc);
        
        $categorie = $this->getCategory();
        $forum = $categorie->getForum();
        
        $doc->forum_id = $forum->getId();
        $doc->forum_name = $forum->getResourceNode()->getName();

        $doc->forum_category_id = $categorie->getId();
        $doc->forum_category_name = $categorie->getName();
        $doc->forum_category_url= $categorie->get('router')->generate('claro_forum_subjects', array(
            'category' => $categorie->getId()
        ));

        $doc->forum_subject_id = $this->getId();
        $doc->forum_subject_name = $this->getTitle();
        $doc->forum_subject_url= $this->get('router')->generate('claro_forum_messages', array(
            'subject' => $doc->forum_subject_id
        ));

        $doc->content_t = $this->getTitle();

        $doc->forum_creator_id = $this->getCreator()->getId();
        $doc->forum_creator_name = $this->getCreator()->getFirstName().' '.$this->getCreator()->getLastName();
        $doc->forum_creator_profil_url =  $this->get('router')->generate('claro_public_profile_view', array(
            'publicUrl' => $this->getCreator()->getPublicUrl()
        ));

        return $doc;
    }
    
    public function getResourceNode()
    {
        return $this->getCategory()->getForum()->getResourceNode();
    }
    
    public function getIsUserLocked() {
        return $this->isUserLocked;
    }

    public function setIsUserLocked($isUserLocked) {
        $this->isUserLocked = $isUserLocked;
    }


}