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

use Claroline\ForumBundle\Entity\Forum;
use Claroline\ForumBundle\Entity\Subject;
use Claroline\ForumBundle\Entity\Category;
use Doctrine\ORM\EntityRepository;
use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;

class CategoryRepository extends EntityRepository {
	
	public function getQueryFindCategoriesByWorkspace(AbstractWorkspace $workspace) {
		$qb = $this->_em->createQueryBuilder();
		$qb->select('c')
			->from('Claroline\ForumBundle\Entity\Category', 'c')
			->join('c.forum', 'f')
			->join('f.resourceNode', 'rn')
			->join('rn.workspace', 'ws', 'with', 'ws = :workspace')
			->setParameter('workspace', $workspace);
		return $qb;
	}
}
