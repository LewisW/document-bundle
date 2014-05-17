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
        if($destination === null) {
            throw new \Exception('PDFConversionDriver requires the $destination argument');
        }

        $dest_extension = pathinfo($destination, PATHINFO_EXTENSION);
        $tmpFile = sys_get_temp_dir() . '/' . pathinfo($source, PATHINFO_FILENAME) . '.' . pathinfo($destination, PATHINFO_EXTENSION);

        $command = sprintf('%s --headless --convert-to %s --outdir %s %s', $this->office_path, $dest_extension, sys_get_temp_dir(), $source);

        //var_dump($command); exit;
        exec($command, $output, $return);
        //var_dump($output, $return);

        if(file_exists($tmpFile)) {
            unlink($source);
            rename($tmpFile,$destination);
        } else {
            throw new \Exception(sprintf('Could not convert %s to %s',$source,$destination));
        }

    }

    public function canConvert($source_extension, $destination_extension)
    {
        if (isset($this->source_formats[$source_extension]) && isset($this->dest_formats[$destination_extension])) {
           return true;
        }

        return false;
    }

    public function getFormats()
    {
        return $this->dest_formats;
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