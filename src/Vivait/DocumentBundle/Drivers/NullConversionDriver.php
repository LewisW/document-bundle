<?php

namespace Vivait\DocumentBundle\Drivers;

class NullConversionDriver implements ConversionDriverInterface
{
    public function canConvert($source_extension, $destination_extension)
    {
        return ($source_extension == $destination_extension);
    }

    public function convert($source, $destination = null)
    {
        return $destination;
    }

    public function getFormats($source_extension = null)
    {
        return [];
    }
}