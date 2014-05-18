<?php

namespace Vivait\DocumentBundle\Drivers;

use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("vivait.document.mailmerge.pdf");
 */
class PDFConversionDriver implements ConversionDriverInterface
{
    private $office_path = '~/soffice';

    private $source_formats = [
      'docx' => 'Word document',
      'zip'  => 'Zip file'
    ];

    private $dest_formats = [
      'pdf' => 'PDF'
    ];

    function __construct($office_path = null)
    {
        if ($office_path) {
            $this->office_path = $office_path;
        }
    }

    public function convert($source, $destination = null) {
        $dest_extension = pathinfo($destination, PATHINFO_EXTENSION);

        $command = sprintf('%s --headless --convert-to %s %s', $this->office_path, $dest_extension, $source);

        //var_dump($command); exit;
        exec($command, $output, $return);

        //var_dump($output, $return);
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

    /**
     * Gets office_path
     * @return null
     */
    public function getOfficePath()
    {
        return $this->office_path;
    }

    /**
     * Sets office_path
     * @param null $office_path
     * @return $this
     * @DI\InjectParams({
     *     "office_path" = @DI\Inject("%open_office_path%")
     * })
     */
    public function setOfficePath($office_path)
    {
        $this->office_path = $office_path;

        return $this;
    }
}