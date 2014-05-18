<?php

namespace Vivait\DocumentBundle\Drivers;

use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("vivait.document.mailmerge.docx");
 */
class DocxConversionDriver implements ConversionDriverInterface
{
    private $source_formats = [
      'docx' => 'Word document',
      'zip'  => 'Zip file',
    ];

    private $dest_formats = [
      'docx' => 'Word document'
    ];

    public function convert($source, $destination = null) {
        return $destination;
    }

    public function canConvert($source_extension, $destination_extension)
    {
        if (isset($this->source_formats[$source_extension]) && isset($this->dest_formats[$destination_extension])) {
           return true;
        }

        return false;
    }

    public function getFormats($source_extension = null)
    {
        return (!$source_extension || isset($this->source_formats[$source_extension])) ? $this->dest_formats : [];
    }
}