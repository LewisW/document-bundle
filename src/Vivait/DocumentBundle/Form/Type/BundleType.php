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
class BundleType extends AbstractType {
	use DeletableTrait;

	public function buildForm(FormBuilderInterface $builder, array $options) {
		$builder->add('name', 'text', array('required' => true));
		$builder->add('category', 'text', array('required' => false));
		$builder->add('enabled', 'checkbox', array('required' => false));
        $builder->add('letters',	'entity', array(
                'class' => 'VivaitDocumentBundle:Letter',
                'property' => 'name',
                'multiple' => true,
                'required' => false,
                'label' => 'Letters',
                'attr'     => array('size' => 20),
                'by_reference' => false											#USE THIS ALONG WITH A $owning->addInverse($this); IN THE addOwning() FUNCTION TO TRIGGER AN UPDATE WHEN ON INVERSE SIDE
            ));

		$builder->addEventListener(FormEvents::PRE_SET_DATA, array($this, 'addDeleteButton'));
	}

	/**
	 * Returns the name of this type.
	 *
	 * @return string The name of this type
	 */
	public function getName() {
		return 'bundle';
	}



	public function createTitle($is_new = false) {
		return $is_new ? 'Add Bundle' : 'Edit Bundle';
	}
}