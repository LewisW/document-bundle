<?php

namespace Vivait\DocumentBundle\Services;

use Doctrine\ORM\EntityManager;
use DocxTemplate\Document;
use DocxTemplate\TemplateFactory;
use MBence\OpenTBSBundle\Services\OpenTBS;
use Twig_Environment;
use Twig_Loader_Array;
use Twig_Loader_Filesystem;
use Twig_Loader_String;
use Vivait\Common\Model\Task\LetterInterface;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("vivait.document.mailmerge");
 */
class MailMergeService
{
    /**
     * @var OpenTBS
     */
    protected $tbs;

    protected $fields = array();

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
        if ($destination === null) {
            $destination = $source;
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

        return $destination;
    }

    public function mergeString($content)
    {
        $loader = new Twig_Loader_String();
        $twig   = new Twig_Environment($loader);

        return $twig->render($content, $this->fields);
    }
} 