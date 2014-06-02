<?php

namespace Vivait\DocumentBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use JMS\DiExtraBundle\Annotation\FormType;
use Symfony\Component\Form\FormEvents;
use Vivait\Common\Form\DeletableTrait;

/**
 * @FormType
 */
class LetterType extends AbstractType {
	use DeletableTrait;

	public function buildForm(FormBuilderInterface $builder, array $options) {
		$builder->add('name', 'text', array('required' => true));
		$builder->add('category', 'text', array('required' => false));
		$builder->add('enabled', 'checkbox', array('required' => false));
		$builder->add('filename', 'text', array('label'=>'Final Filename', 'required' => true));
		$builder->add('file', 'file', array('label'=>'Upload New File','error_bubbling'=>true));

		$builder->addEventListener(FormEvents::PRE_SET_DATA, array($this, 'addDeleteButton'));
	}

	/**
	 * Returns the name of this type.
	 *
	 * @return string The name of this type
	 */
	public function getName() {
		return 'letter';
	}



	public function createTitle($is_new = false) {
		return $is_new ? 'Add document' : 'Edit document';
	}
}