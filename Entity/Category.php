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
use Doctrine\Common\Collections\ArrayCollection;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;
use Claroline\CoreBundle\Entity\Resource\AbstractIndexableResourceElement;

/**
 * @ORM\Entity(repositoryClass="Claroline\ForumBundle\Repository\CategoryRepository")
 * @ORM\Table(name="claro_forum_category")
 */
class Category extends AbstractIndexableResourceElement
{
    
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\ForumBundle\Entity\Forum",
     *     inversedBy="categories"
     * )
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    protected $forum;

    /**
     * @ORM\OneToMany(
     *     targetEntity="Claroline\ForumBundle\Entity\Subject",
     *     mappedBy="category", 
     *     cascade={"remove"}
     * )
     * @ORM\OrderBy({"id" = "ASC"})
     */
    protected $subjects;

    /**
     * @ORM\Column(name="created", type="datetime")
     * @Gedmo\Timestampable(on="create")
     */
    protected $creationDate;

    /**
     * @ORM\Column(type="datetime")
     * @Gedmo\Timestampable(on="update")
     */
    protected $modificationDate;

    /**
     * @ORM\Column()
     * @Assert\NotBlank()
     */
    protected $name;
    
    /**
     * @ORM\Column(type="boolean")
     */
    protected $isUserLocked = false;

    public function __construct()
    {
        $this->subjects = new ArrayCollection;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setForum(Forum $forum)
    {
        $this->forum = $forum;
    }

    public function getForum()
    {
        return $this->forum;
    }

    public function addSubject(Subject $subject)
    {
        $this->subjects->add($subject);
    }

    public function getSubjects()
    {
        return $this->subjects;
    }

    public function removeSubject(Subject $subject)
    {
        $this->subjects->remove($subject);
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }
    public function getCreationDate()
    {
        return $this->creationDate;
    }

    public function getModificationDate()
    {
        return $this->modificationDate;
    }

    public function setCreationDate($creationDate)
    {
        $this->creationDate = $creationDate;
    }

    public function setModificationDate($modificationDate)
    {
        $this->modificationDate = $modificationDate;
    }

    public function fillIndexableDocument(&$doc)
    {
        $doc = parent::fillIndexableDocument($doc);
        
        $categorie = $this;
        $forum = $categorie->getForum();
        
        $doc->forum_id = $forum->getId();
        $doc->forum_name = $forum->getResourceNode()->getName();

        $doc->forum_category_id = $categorie->getId();
        $doc->forum_category_name = $categorie->getName();
        $doc->forum_category_url= $categorie->get('router')->generate('claro_forum_subjects', array(
            'category' => $categorie->getId()
        ));
        
        $doc->content_t = $this->getName();

        return $doc;
    }

    public function getResourceNode()
    {
        return $this->getForum()->getResourceNode();
    }
    
    public function getForumNameAndCategoryName() {
    	return $this->getForum()->getResourceNode()->getName()." -- ".$this->getName();
    }
    
    public function getIsUserLocked() {
        return $this->isUserLocked;
    }

    public function setIsUserLocked($isUserLocked) {
        $this->isUserLocked = $isUserLocked;
        
        return $this;
    }


}