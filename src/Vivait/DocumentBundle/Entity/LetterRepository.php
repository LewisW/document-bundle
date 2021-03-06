<?php

namespace Vivait\DocumentBundle\Entity;

use Doctrine\ORM\EntityRepository;

class LetterRepository extends EntityRepository {
	public function create() {
		$letter = new Letter();

		return $letter;
	}

	public function save(Letter $letter) {
		$em = $this->getEntityManager();

		$em->persist($letter);
		$em->flush();
	}

	public function delete(Letter $letter) {
		$em = $this->getEntityManager();

		$em->remove($letter);
		$em->flush();
	}

    public function findAll() {
        $em = $this->getEntityManager();
        return $em->createQueryBuilder('l')
            ->select('l')
            ->from('VivaitDocumentBundle:Letter','l')
            ->orderBy('l.priority','DESC')
            ->getQuery()
            ->getResult();
    }
} 