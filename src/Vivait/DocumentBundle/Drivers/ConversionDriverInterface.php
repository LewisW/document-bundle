<?php


namespace Vivait\DocumentBundle\Drivers;


interface ConversionDriverInterface {

    /**
     * @param string $source_extension
     * @param string $destination_extension
     * @return boolean
     */
    public function canConvert($source_extension, $destination_extension);

    /**
     * @param string $source
     * @param null|string $destination
     * @return string
     */
    public function convert($source, $destination = null);

    public function getFormats();
}