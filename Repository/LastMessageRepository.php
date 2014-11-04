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
use Claroline\ForumBundle\Entity\Category;

class LastMessageRepository extends EntityRepository {

	public function deleteAllForCategory(Category $category) {
		$dql = "DELETE FROM Claroline\ForumBundle\Entity\LastMessage lm
				WHERE lm.category = :category";
		
		return $this->_em->createQuery($dql)->execute(array("category" => $category));
	}
}
