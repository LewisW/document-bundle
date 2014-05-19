<?php

namespace Vivait\DocumentBundle\Drivers;

use JMS\DiExtraBundle\Annotation as DI;
use Vivait\DocumentBundle\Exception\InvalidDocumentException;

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

        $dest_directory = pathinfo($destination, PATHINFO_DIRNAME);
        $command = sprintf('export HOME=/tmp
                            %s --headless --invisible --norestore --convert-to %s --outdir %s %s',
          escapeshellcmd($this->office_path), escapeshellarg($dest_extension),
          escapeshellarg($dest_directory), escapeshellarg($source));

        exec($command);

        if (!file_exists($destination)) {
            throw new InvalidDocumentException(sprintf('Could not convert %s to %s', $source, $destination));
        }
        else {
            //unlink($source);
        }

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