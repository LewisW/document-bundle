<?php

namespace Vivait\DocumentBundle\Services;

use DocxTemplate\Document;
use MBence\OpenTBSBundle\Services\OpenTBS;
use Vivait\DocumentBundle\Drivers\ConversionDriverInterface;
use Twig_Environment;
use Twig_Loader_Array;
use Twig_Loader_String;
use JMS\DiExtraBundle\Annotation as DI;
use Vivait\DocumentBundle\Drivers\DocxConversionDriver;
use Vivait\DocumentBundle\Drivers\NullConversionDriver;
use Vivait\DocumentBundle\Drivers\PDFConversionDriver;

/**
 * @DI\Service("vivait.document.mailmerge");
 */
class MailMergeService
{
    /**
     * @var ConversionDriverInterface[]
     */
    private $drivers;

    /**
     * @return array
     */
    public function getDriverFormats($extension = null) {
        $formats = [];
        foreach ($this->getDrivers() as $driver) {
            $formats += $driver->getFormats($extension);
        }

        return $formats;
    }

    /**
     * @var OpenTBS
     */
    protected $tbs;

    protected $fields = array();

    /**
     * @todo: Move this to a generic tagging service
     * @param \Vivait\DocumentBundle\Drivers\PDFConversionDriver $PDFConversionDriver
     * @param \Vivait\DocumentBundle\Drivers\DocxConversionDriver $docxConversionDriver
     * @internal param $pdfdriver
     *
     * @DI\InjectParams({
     *  "PDFConversionDriver" = @DI\Inject("vivait.document.mailmerge.pdf"),
     *  "docxConversionDriver" = @DI\Inject("vivait.document.mailmerge.docx")
     * })
     */
    function addDefaultDrivers(PDFConversionDriver $PDFConversionDriver, DocxConversionDriver $docxConversionDriver) {
        $this->addDriver($PDFConversionDriver, 'pdf');
        $this->addDriver($docxConversionDriver, 'docx');
        $this->addDriver(new NullConversionDriver(), 'null');
    }

    function addFields($fields)
    {
        $this->fields = array_merge($this->fields, $fields);
    }

    /**
     * Flattens a nested array into a one-dimensional array while concat keys
     * @param        $array
     * @param string $prefix
     * @param string $separator
     * @return array
     */
    public static function flatten($array, $prefix = '', $separator = '.')
    {
        $result = array();
        foreach ($array as $key => $value) {
            $new_prefix = $prefix . $key;

            if (is_array($value)) {
                $result += self::flatten($value, $new_prefix . $separator, $separator);
            } else if (is_object($value) && $value instanceOf \DateTime) {
                $result += self::flatten(self::getDateFormats($value), $new_prefix . $separator, $separator);
            } else {
                $result[$new_prefix] = $value;
            }
        }
        return $result;
    }
//
//    protected function extractRootsWalker($data, $roots, &$top) {
//        foreach ($data as $key => $value) {
//            if (is_array($value)) {
//                $value = $this->extractRootsWalker($value, $roots, $top);
//            }
//
//            if (in_array($key, $roots, true)) {
//                var_dump($key);
//                $top[$key] = $value;
//                unset($data[$key]);
//            }
//        }
//
//        return $data;
//    }

    public function extractRoots($data, $roots, $parent_key = null) {
        $return = [];
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $return += $this->extractRoots($value, $roots, $key);
            }

            if (!$parent_key || in_array($key, $roots, true)) {
                $return[$key] = $value;
            }
            else {
                $return[$parent_key][$key] = $value;
            }
        }

        var_dump($return);

        return $return;


//        $top = $data;
//
//        $this->extractRootsWalker($data, $roots, $top);
//var_dump(array_keys($top));
//        return $top;

        if ($data === null) {
            return [];
        }

        if ($top === null) {
            $data = $top;
        }

        // Loop through each element
        foreach ($data as $key => $value) {
            // Call it recursively
            if (is_array($value) && $key == 'deal') {
                $data[$key] = $this->extractRoots($value, $roots, $top);
            }
            else {
                $data[$key] = $value;
            }

            // Is it an array
            if (in_array($key, $roots, true)) {
                $top[$key] = $this->extractRoots($value, $roots, $top);
            }
        }

        return $return;
    }

    /**
     * Converts a date into various formats
     * @param \DateTime $date
     * @param array $formats
     * @return array
     */
    public static function getDateFormats(\DateTime $date, $formats = array())
    {
        $formats = array_merge(
          $formats,
          array(
            'c'    => 'c',
            'full' => 'd/m/Y H:i:s',
            'date' => 'd/m/Y',
            'time' => 'H:i:s'
          )
        );

        $return = array();

        foreach ($formats as $key => $value) {
            if ($date) {
                $return[$key] = $date->format($value);
            } else {
                $return[$key] = null;
            }
        }

        return $return;
    }

    public function cleanXML($contents) {
//        $dom = new \SimpleXMLElement($contents);
//        $buffer = '';
//        $start = null;
//
//        // Loop through all the text
//        foreach ($dom->xpath('//w:t') as $text) {
//            $start = strpos($text, '{');
//
//            // It has an opening tag
//            if ($start !== false) {
//                // Check if the next character is a twig character
//                if (!isset($text[$start + 1])) {
//                    continue;
//                }
//                elseif ($text[$start + 1] == '%') {
//
//                }
//            }
//            var_dump((string)$text);
//        }
//
//        exit;

        return preg_replace_callback('#«([a-zA-Z0-9_]+)»#', function ($match) {
              return sprintf('{{ %s|default() }}', str_replace('_', '.', $match[1]));
          }, $contents);

        $contents = preg_match('#\{(?:\{|%)(.*?)(?:\}|%)\}#i', function($match) {
              $match = $match[0];
              $stripped = strip_tags($match);

              if ($stripped != $match) {
                  return $stripped;
              }

              return $match;
          }, $contents);

        // Replace the mail merge tags
        $contents = preg_replace('#«([a-z0-9\.]+)»#i', '{{ $1 }}', $contents);

        return $contents;
    }

    public function mergeFile($source, $destination = null)
    {
        $driver = new NullConversionDriver();

        if ($destination === null) {
            $destination = $source;
        }
        else {
            $source_extension = pathinfo($source, PATHINFO_EXTENSION);
            $destination_extension = pathinfo($destination, PATHINFO_EXTENSION);

            if ($source_extension != $destination_extension) {
                $driver = $this->getConversionDriver($source_extension, $destination_extension);
            }
        }

        // TODO: This should be driver based
        $document = new Document($source);

        $loader = new Twig_Loader_Array([
              'base.html' => $this->cleanXML($document->getContent())
        ]);

        $twig   = new Twig_Environment($loader);

        $render = $twig->render('base.html', $this->fields);

        $document->setContent($render)
        ->save($destination);

        $driver->convert($source, $destination);

        return $destination;
    }

    public function mergeString($content)
    {
        $loader = new Twig_Loader_String();
        $twig   = new Twig_Environment($loader);

        return $twig->render($content, $this->fields);
    }

    /**
     * @param $driver
     * @param null $alias
     * @return $this
     */
    public function addDriver($driver, $alias = null) {
        if (is_array($driver) || $driver instanceOf \Traversable) {
            foreach ($driver as $key => $value) {
                $this->addDriver($value, $key);
            }
        }
        else if ($driver instanceOf ConversionDriverInterface && $alias) {
            $this->drivers[$alias] = $driver;
        }

        return $this;
    }

    /**
     * Gets drivers
     * @return ConversionDriverInterface[]
     */
    public function getDrivers()
    {
        return $this->drivers;
    }

    /**
     * @param $name
     */
    public function getDriver($name) {
        if (isset($this->drivers[$name])) {
            return $this->drivers[$name];
        }

        throw new \OutOfBoundsException(sprintf('Invalid driver %s', $name));
    }

    private function getConversionDriver($source_extension, $destination_extension) {
        foreach ($this->getDrivers() as $driver) {
            if ($driver->canConvert($source_extension, $destination_extension)) {
                return $driver;
            }
        }
    }
} 