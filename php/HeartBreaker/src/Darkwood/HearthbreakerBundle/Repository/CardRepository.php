<?php

namespace Darkwood\HearthbreakerBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * CardRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class CardRepository extends EntityRepository
{
	public function count()
	{
		$qb = $this->createQueryBuilder('c')
			->select('COUNT(c.id) as nb')
		;

		$count = $qb->getQuery()->getScalarResult();

		return $count[0]['nb'];
	}
}
