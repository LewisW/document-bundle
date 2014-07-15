<?php

namespace Vivait\DocumentBundle\Event;

use Vivait\Common\Event\EntityEvent;

class LetterEvent extends EntityEvent {
    const EVENT_ENTITY_CREATED  = 'vivait.created.letter';
    const EVENT_ENTITY_MODIFIED = 'vivait.modified.letter';
    const EVENT_ENTITY_DELETED  = 'vivait.deleted.letter';

    public static function getEntityTypeLabel() {
		return 'document';
	}
}