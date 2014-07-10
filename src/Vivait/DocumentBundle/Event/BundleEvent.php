<?php

namespace Vivait\DocumentBundle\Event;

use Vivait\Common\Event\EntityEvent;

class BundleEvent extends EntityEvent {
	public function getEntityName() {
		return 'bundle';
	}
}