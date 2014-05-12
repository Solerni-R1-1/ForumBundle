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
use Claroline\CoreBundle\Entity\AbstractIndexable;

/**
 * @ORM\Entity
 * @ORM\Table(name="claro_forum_category")
 */
class Category extends AbstractIndexable
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
     *     mappedBy="category"
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
        
        $doc->forum_id = $this->getForum()->getId();
        $doc->forum_name = $this->getForum()->getResourceNode()->getName();
        $doc->content = $this->getName();
        $resourceNode = $this->getForum()->getResourceNode();
        $doc->resource_id = $resourceNode->getId();
        $doc->wks_id = $resourceNode->getWorkspace()->getId();
        $doc->creation_date = $this->getCreationDate();
        $doc->modification_date = $this->getModificationDate();
        $doc->type_name = $resourceNode->getResourceType()->getName().'_category';
        $doc->owner_id = $resourceNode->getCreator()->getId();
        $doc->owner_name = $resourceNode->getCreator()->getFirstName() . ' ' .
                           $resourceNode->getCreator()->getLastName();
        return $doc;
    }

}