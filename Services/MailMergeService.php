<?php

namespace Vivait\DocumentBundle\Services;

use Doctrine\ORM\EntityManager;
use MBence\OpenTBSBundle\Services\OpenTBS;
use Vivait\Common\Model\Task\LetterInterface;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("vivait.document.mailmerge");
 */
class MailMergeService {
	/**
	 * @var OpenTBS
	 */
	protected $tbs;

	protected $fields = array();

	/**
	 * @DI\InjectParams({
	 *     "tbs" = @DI\Inject("opentbs")
	 * })
	 */
	function __construct(OpenTBS $tbs) {
		$this->tbs = $tbs;
	}

	function addFields($fields) {
		$this->fields = array_merge($this->fields, $fields);
	}

	/**
	 * Flattens a nested array into a one-dimensional array while concat keys
	 * @param        $array
	 * @param string $prefix
	 * @param string $separator
	 * @return array
	 */
	public static function flatten($array, $prefix = '', $separator = '.') {
		$result = array();
		foreach ($array as $key => $value) {
			$new_prefix = $prefix . $key;

			if (is_array($value)) {
				$result += self::flatten($value, $new_prefix . $separator);
			}
			else if (is_object($value) && $value instanceOf \DateTime) {
				$result += self::flatten(self::getDateFormats($value), $new_prefix . $separator);
			}
			else {
				$result[$new_prefix] = $value;
			}
		}
		return $result;
	}

	/**
	 * Converts a date into various formats
	 * @param \DateTime $date
	 * @return array
	 */
	public static function getDateFormats(\DateTime $date, $formats = array()) {
		$formats = array_merge($formats, array(
			'c'    => 'c',
			'full' => 'd/m/Y H:i:s',
			'date' => 'd/m/Y',
			'time' => 'H:i:s'
		));

		$return        = array();

		foreach ($formats as $key => $value) {
			if ($date) {
				$return[$key] = $date->format($value);
			} else {
				$return[$key] = null;
			}
		}

		return $return;
	}

	public function mergeFile($source, $destination = null) {
		if ($destination === null) {
			$destination = $source;
		}

		$this->tbs->LoadTemplate($source);
		$this->tbs->SetOption([
			'chr_open'  => '{{ ',
			'chr_close' => ' }}',
			'noerr'     => true
		]);

		// Flatten and merge the fields
		foreach ($this->fields as $base => $group) {
			$this->tbs->MergeField($base, $group);
		}

		$this->tbs->Show(OPENTBS_FILE, $destination);

		return $destination;
	}

	public function mergeString($content) {
		$fields = self::flatten($this->fields);

		foreach ($fields as $search => $replace) {
			$content = str_replace(sprintf("{{ %s }}", $search), $replace, $content);
		}

		return $content;
	}
} 