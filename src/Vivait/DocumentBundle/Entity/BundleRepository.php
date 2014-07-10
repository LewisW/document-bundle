<?php

namespace Vivait\DocumentBundle\Entity;

use Doctrine\ORM\EntityRepository;

class BundleRepository extends EntityRepository {

	public function create() {
		$bundle = new Bundle();

		return $bundle;
	}

	public function save(Bundle $bundle) {
		$em = $this->getEntityManager();

		$em->persist($bundle);
		$em->flush();
	}

	public function delete(Bundle $bundle) {
		$em = $this->getEntityManager();

		$em->remove($bundle);
		$em->flush();
	}
} 