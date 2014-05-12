<?php

namespace Vivait\DocumentBundle\Library;

use JMS\Serializer\Naming\IdenticalPropertyNamingStrategy;
use JMS\Serializer\SerializerBuilder;
use Vivait\DocumentBundle\Library\SimpleSerializationVisitor;

class SimpleSerializerBuilder
{

    public static function build()
    {
        return SerializerBuilder::create()
          ->addDefaultHandlers()
          ->setPropertyNamingStrategy($namingStrategy = new IdenticalPropertyNamingStrategy())
          ->addDefaultSerializationVisitors()
          ->addDefaultListeners()
          ->setSerializationVisitor('json', new SimpleSerializationVisitor($namingStrategy))
          ->addDefaultDeSerializationVisitors()
          ->build();
    }
}