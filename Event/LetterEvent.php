<?php

namespace Vivait\DocumentBundle\Event;

use Vivait\Common\Event\EntityEvent;

class LetterEvent extends EntityEvent {
	public function getEntityName() {
		return 'document';
	}
}