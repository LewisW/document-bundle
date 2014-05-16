<?php

namespace Vivait\DocumentBundle\Library;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\Context;
use JMS\Serializer\GenericSerializationVisitor;
use JMS\Serializer\Metadata\PropertyMetadata;

class SimpleSerializationVisitor extends GenericSerializationVisitor
{
    private $resolveNullClasses = false;

    public function getResult()
    {
        return $this->getRoot();
    }

    /**
     * Gets resolveNullClasses
     * @return boolean
     */
    public function getResolveNullClasses()
    {
        return $this->resolveNullClasses;
    }

    /**
     * Sets resolveNullClasses
     * @param boolean $resolveNullClasses
     * @return $this
     */
    public function setResolveNullClasses($resolveNullClasses)
    {
        $this->resolveNullClasses = $resolveNullClasses;

        return $this;
    }

    public function visitProperty(PropertyMetadata $metadata, $data, Context $context)
    {
        $v = $metadata->getValue($data);

        if ($v === null && $this->resolveNullClasses) {
            $reader = new AnnotationReader();
            $annotations = $reader->getPropertyAnnotations($metadata->reflection);

            foreach ($annotations as $annotation) {
                if ($annotation instanceOf \Doctrine\ORM\Mapping\OneToOne || $annotation instanceOf \Doctrine\ORM\Mapping\ManyToOne || $annotation instanceOf \Doctrine\ORM\Mapping\ManyToMany || $annotation instanceOf \Doctrine\ORM\Mapping\OneToMany) {
                    $namespaced = (strpos($annotation->targetEntity, '\\') === false ? 'Viva\BravoBundle\Entity\\' : '') . $annotation->targetEntity;

                    $v = new \StdClass();
                    $type = array('name' => $namespaced, 'params' => array());

                    $v = $this->getNavigator()->accept($v, $type, $context);
                }

            }
        }

        $v = $this->getNavigator()->accept($v, $metadata->type, $context);

        if (null === $v && !$context->shouldSerializeNull()) {
            return;
        }

        $k = $this->namingStrategy->translateName($metadata);

        if ($metadata->inline) {
            if (is_array($v)) {
                array_walk(
                  $v,
                  function ($value, $key) {
                      $this->addData($key, $value);
                  }
                );
            }
        } else {
            $this->addData($k, $v);
        }
    }
}
