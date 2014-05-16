<?php

namespace Vivait\DocumentBundle\Drivers;

class NullConversionDriver implements ConversionDriverInterface
{
    public function transform($source, $destination = null) {
        return $destination;
    }

    public function canConvert($source_extension, $destination_extension)
    {
        return false;
    }

    public function convert($source, $destination = null)
    {
        // TODO: Implement convert() method.
    }

    public function getFormats()
    {
        return [];
    }
}